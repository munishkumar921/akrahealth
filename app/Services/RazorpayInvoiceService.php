<?php

namespace App\Services;

use App\Mail\RazorpayInvoiceMail;
use App\Models\Appointment;
use App\Models\BillingCore;
use App\Models\Hospital;
use App\Models\Invoice;
use App\Models\RazorpayInvoice;
use App\Models\Transaction;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RazorpayInvoiceService
{
    protected string $keyId;

    protected string $keySecret;

    protected string $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key');
        $this->keySecret = config('services.razorpay.secret');
        $this->baseUrl = config('services.razorpay.base_url', 'https://api.razorpay.com/v1');
    }

    /**
     * Create a Razorpay Invoice
     */
    public function createInvoice(Invoice $invoice): array
    {
        $customerDetails = $this->resolveCustomerDetails($invoice);

        if (empty($customerDetails['email'])) {
            return ['success' => false, 'error' => 'Customer email is required'];
        }

        $lineItems = $this->prepareLineItems($invoice);
        $totalAmount = (int) round(($invoice->total_amount ?? 0) * 100);

        if ($totalAmount < 100) {
            return ['success' => false, 'error' => 'Amount must be at least ₹1.00'];
        }

        $dueDate = $invoice->due_date?->isPast() ? now()->addDays(7) : ($invoice->due_date ?? now()->addDays(7));

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post("{$this->baseUrl}/invoices", [
                'type' => 'invoice',
                'date' => time(),
                'customer' => [
                    'name' => $customerDetails['name'] ?? 'Customer',
                    'email' => $customerDetails['email'],
                    'contact' => $customerDetails['phone'] ?? '',
                ],
                'line_items' => $lineItems,
                'currency' => $invoice->currency ?? 'INR',
                'partial_payment' => false,
                'comment' => $invoice->notes ?? '',
                'terms' => $invoice->terms_conditions ?? 'Payment due within 7 days.',
            ]);

        if (! $response->successful()) {
            Log::error('Razorpay: Invoice creation failed', [
                'invoice_id' => $invoice->id,
                'error' => $response->body(),
            ]);

            return ['success' => false, 'error' => 'Failed to create invoice'];
        }

        $razorpayData = $response->json();

        // Update invoice with Razorpay ID
        $invoice->update([
            'razorpay_invoice_id' => $razorpayData['id'],
            'razorpay_customer_id' => $razorpayData['customer_id'] ?? null,
        ]);

        // Create RazorpayInvoice record
        $this->syncRazorpayInvoice($invoice, $razorpayData);

        Log::info('Razorpay: Invoice created', [
            'razorpay_invoice_id' => $razorpayData['id'],
            'short_url' => $razorpayData['short_url'] ?? null,
        ]);

        return [
            'success' => true,
            'data' => $razorpayData,
            'short_url' => $razorpayData['short_url'] ?? null,
        ];
    }

    /**
     * Send invoice via email
     */
    public function sendInvoice(Invoice $invoice): array
    {
        $razorpayInvoiceId = $invoice->razorpay_invoice_id;

        if (! $razorpayInvoiceId) {
            $result = $this->createInvoice($invoice);
            if (! $result['success']) {
                return $result;
            }
            $razorpayInvoiceId = $result['data']['id'];
        }

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post("{$this->baseUrl}/invoices/{$razorpayInvoiceId}/notify_by", [
                'medium' => ['email'],
            ]);

        if ($response->successful()) {
            $invoice->markAsSent();
            $this->sendCustomEmail($invoice);

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to send invoice'];
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(Invoice $invoice): array
    {
        $razorpayInvoiceId = $invoice->razorpay_invoice_id;

        if (! $razorpayInvoiceId) {
            $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

            return ['success' => true];
        }

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post("{$this->baseUrl}/invoices/{$razorpayInvoiceId}/cancel");

        if ($response->successful()) {
            $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
            RazorpayInvoice::where('razorpay_invoice_id', $razorpayInvoiceId)
                ->update(['status' => RazorpayInvoice::STATUS_CANCELLED]);

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to cancel invoice'];
    }

    /**
     * Handle webhook events
     */
    public function handleWebhook(array $payload): ?Invoice
    {
        $event = $payload['event'] ?? '';
        $entity = $payload['payload']['invoice']['entity'] ?? [];
        $razorpayInvoiceId = $entity['id'] ?? null;

        if (! $razorpayInvoiceId) {
            Log::warning('Razorpay: Webhook without invoice ID');

            return null;
        }

        $invoice = $this->findInvoice($razorpayInvoiceId);
        if (! $invoice) {
            $invoice = $this->findOrCreateSubscriptionInvoiceFromWebhook($entity);
        }

        if (! $invoice) {
            Log::warning('Razorpay: Invoice not found', ['razorpay_invoice_id' => $razorpayInvoiceId]);
            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'Invoice sync failed',
                    'A Razorpay webhook referenced an invoice that could not be mapped locally.',
                    'invoice_sync_failed',
                    [
                        'meta' => [
                            'razorpay_invoice_id' => $razorpayInvoiceId,
                            'event' => $event,
                        ],
                    ]
                )
            );

            return null;
        }

        // Sync webhook data
        $this->syncRazorpayInvoice($invoice, $entity);

        // Handle event
        match ($event) {
            'invoice.paid' => $this->handlePaid($invoice, $entity),
            'invoice.partially_paid' => $this->handlePartiallyPaid($invoice, $entity),
            'invoice.viewed' => $this->handleViewed($invoice),
            'invoice.cancelled' => $this->handleCancelled($invoice),
            'invoice.expired' => $this->handleExpired($invoice),
            default => Log::info('Razorpay: Unhandled event', ['event' => $event]),
        };

        return $invoice;
    }

    /**
     * Find invoice by Razorpay invoice ID
     */
    protected function findInvoice(string $razorpayInvoiceId): ?Invoice
    {
        $invoice = Invoice::where('razorpay_invoice_id', $razorpayInvoiceId)->first();

        if (! $invoice) {
            $razorpayInvoice = RazorpayInvoice::where('razorpay_invoice_id', $razorpayInvoiceId)->first();
            if ($razorpayInvoice && $razorpayInvoice->invoice_id) {
                $invoice = Invoice::find($razorpayInvoice->invoice_id);
            }
        }

        return $invoice;
    }

    protected function findOrCreateSubscriptionInvoiceFromWebhook(array $entity): ?Invoice
    {
        $razorpaySubscriptionId = $entity['subscription_id'] ?? null;
        if (! $razorpaySubscriptionId) {
            return null;
        }

        $subscription = UserSubscription::with(['subscriptionPlan', 'user'])
            ->where('razorpay_subscription_id', $razorpaySubscriptionId)
            ->first();

        if (! $subscription) {
            return null;
        }

        $invoice = Invoice::where('subscription_id', $subscription->id)
            ->where('razorpay_invoice_id', $entity['id'] ?? null)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        $hospitalId = Hospital::where('user_id', $subscription->user_id)->value('id');
        $customerDetails = $entity['customer_details'] ?? [
            'name' => $subscription->user?->name,
            'email' => $subscription->user?->email,
            'contact' => $subscription->user?->mobile,
        ];

        return Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $subscription->user_id,
            'hospital_id' => $hospitalId,
            'subscription_id' => $subscription->id,
            'amount' => ($entity['amount'] ?? 0) / 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => ($entity['amount'] ?? 0) / 100,
            'currency' => $entity['currency'] ?? ($subscription->currency ?? 'INR'),
            'status' => $this->mapInvoiceStatusToLocal($entity['status'] ?? Invoice::STATUS_DRAFT),
            'razorpay_invoice_id' => $entity['id'] ?? null,
            'razorpay_payment_id' => $entity['payment_id'] ?? null,
            'razorpay_order_id' => $entity['order_id'] ?? null,
            'razorpay_customer_id' => $entity['customer_id'] ?? null,
            'payment_method' => $entity['payments'][0]['method'] ?? 'razorpay',
            'due_date' => isset($entity['expire_by']) ? Carbon::createFromTimestamp($entity['expire_by'])->toDateString() : now()->toDateString(),
            'paid_at' => ! empty($entity['paid_at']) ? Carbon::createFromTimestamp($entity['paid_at']) : null,
            'sent_at' => ! empty($entity['issued_at']) ? Carbon::createFromTimestamp($entity['issued_at']) : now(),
            'items' => $entity['line_items'] ?? [[
                'name' => $subscription->subscriptionPlan?->title ?? 'Subscription Renewal',
                'description' => 'Auto-renewed subscription invoice',
                'quantity' => 1,
                'unit_price' => ($entity['amount'] ?? 0) / 100,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ]],
            'customer_details' => $customerDetails,
            'notes' => 'Subscription webhook invoice for '.$subscription->id,
            'terms_conditions' => '',
            'created_by' => $subscription->user_id,
            'updated_by' => $subscription->user_id,
        ]);
    }

    /**
     * Sync RazorpayInvoice record
     */
    protected function syncRazorpayInvoice(Invoice $invoice, array $razorpayData): void
    {
        RazorpayInvoice::updateOrCreate(
            ['razorpay_invoice_id' => $razorpayData['id']],
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'patient_id' => $invoice->patient_id,
                'user_id' => $invoice->user_id,
                'razorpay_customer_id' => $razorpayData['customer_id'] ?? null,
                'status' => $this->mapStatus($razorpayData['status'] ?? 'draft'),
                'currency' => $razorpayData['currency'] ?? 'INR',
                'amount' => ($razorpayData['amount'] ?? 0) / 100,
                'amount_paid' => ($razorpayData['amount_paid'] ?? 0) / 100,
                'amount_due' => ($razorpayData['amount_due'] ?? 0) / 100,
                'customer_details' => $razorpayData['customer'] ?? [],
                'short_url' => $razorpayData['short_url'] ?? null,
                'pdf_url' => $razorpayData['pdf_url'] ?? null,
                'invoice_date' => isset($razorpayData['date']) ? date('Y-m-d H:i:s', $razorpayData['date']) : now(),
                'due_date' => isset($razorpayData['due_by']) ? date('Y-m-d H:i:s', $razorpayData['due_by']) : null,
                'paid_at' => $razorpayData['status'] === 'paid' ? now() : null,
            ]
        );
    }

    /**
     * Handle invoice paid event
     */
    protected function handlePaid(Invoice $invoice, array $entity): void
    {
        $paymentId = $entity['payments'][0]['id'] ?? null;
        $paymentMethod = $entity['payments'][0]['method'] ?? null;

        $invoice->markAsPaid($paymentId, $paymentMethod);
        $this->createTransaction($invoice, $paymentId, $paymentMethod);
        $this->updateRelatedRecords($invoice);
        $this->syncSubscriptionRenewalFromInvoiceEntity($entity, $invoice);
        $this->sendPaymentEmail($invoice);

        Log::info('Razorpay: Invoice paid', [
            'invoice_id' => $invoice->id,
            'payment_id' => $paymentId,
        ]);
    }

    /**
     * Handle invoice partially paid event
     */
    protected function handlePartiallyPaid(Invoice $invoice, array $entity): void
    {
        $paymentId = $entity['payments'][0]['id'] ?? null;
        $paymentAmount = ($entity['payments'][0]['amount'] ?? 0) / 100;

        $invoice->update(['status' => Invoice::STATUS_PARTIAL]);
        $this->createTransaction($invoice, $paymentId, 'online', $paymentAmount);

        Log::info('Razorpay: Invoice partially paid', [
            'invoice_id' => $invoice->id,
            'amount_paid' => $paymentAmount,
        ]);
    }

    /**
     * Handle invoice viewed event
     */
    protected function handleViewed(Invoice $invoice): void
    {
        $invoice->update([
            'status' => Invoice::STATUS_VIEWED,
            'viewed_at' => now(),
        ]);
        RazorpayInvoice::where('razorpay_invoice_id', $invoice->razorpay_invoice_id)
            ->update(['status' => RazorpayInvoice::STATUS_ISSUED]);
    }

    /**
     * Handle invoice cancelled event
     */
    protected function handleCancelled(Invoice $invoice): void
    {
        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
        RazorpayInvoice::where('razorpay_invoice_id', $invoice->razorpay_invoice_id)
            ->update(['status' => RazorpayInvoice::STATUS_CANCELLED]);
    }

    /**
     * Handle invoice expired event
     */
    protected function handleExpired(Invoice $invoice): void
    {
        $invoice->update(['status' => Invoice::STATUS_OVERDUE]);
        RazorpayInvoice::where('razorpay_invoice_id', $invoice->razorpay_invoice_id)
            ->update(['status' => RazorpayInvoice::STATUS_EXPIRED, 'expired_at' => now()]);
    }

    /**
     * Create transaction record
     */
    protected function createTransaction(Invoice $invoice, ?string $paymentId, ?string $method, ?float $amount = null): void
    {
        if ($paymentId) {
            Transaction::updateOrCreate(
                ['transaction_id' => $paymentId],
                [
                    'invoice_id' => $invoice->id,
                    'patient_id' => $invoice->patient_id,
                    'user_id' => $invoice->user_id,
                    'payment_type' => 'razorpay',
                    'amount' => $amount ?? $invoice->total_amount,
                    'currency' => $invoice->currency,
                    'status' => 'completed',
                    'razorpay_invoice_id' => $invoice->razorpay_invoice_id,
                    'razorpay_order_id' => $invoice->razorpay_order_id,
                    'razorpay_payment_id' => $paymentId,
                    'payment_method' => $method ?? 'razorpay',
                    'notes' => 'Payment via Razorpay: '.$invoice->invoice_number,
                ]
            );

            return;
        }

        $existingTransaction = Transaction::where('invoice_id', $invoice->id)
            ->where('status', 'completed')
            ->where('payment_type', 'razorpay')
            ->first();

        if (! $existingTransaction) {
            Transaction::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'user_id' => $invoice->user_id,
                'payment_type' => 'razorpay',
                'amount' => $amount ?? $invoice->total_amount,
                'currency' => $invoice->currency,
                'status' => 'completed',
                'payment_method' => $method ?? 'razorpay',
                'notes' => 'Payment via Razorpay: '.$invoice->invoice_number,
            ]);
        }
    }

    /**
     * Update related records (appointments, subscriptions, etc.)
     */
    protected function updateRelatedRecords(Invoice $invoice): void
    {
        if ($invoice->appointment_id) {
            \App\Models\Appointment::where('id', $invoice->appointment_id)->update([
                'payment_status' => 'paid',
            ]);
        }

        if ($invoice->subscription_id) {
            \App\Models\UserSubscription::where('id', $invoice->subscription_id)->update([
                'payment_status' => 'paid',
            ]);
        }
    }

    /**
     * Send payment confirmation email
     */
    protected function sendPaymentEmail(Invoice $invoice): void
    {
        $customerDetails = $invoice->customer_details ?? $this->resolveCustomerDetails($invoice);
        $email = $customerDetails['email'] ?? null;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new RazorpayInvoiceMail($invoice));
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to send payment email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send custom invoice email
     */
    protected function sendCustomEmail(Invoice $invoice): void
    {
        $customerDetails = $invoice->customer_details ?? $this->resolveCustomerDetails($invoice);
        $email = $customerDetails['email'] ?? null;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new RazorpayInvoiceMail($invoice));
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolve customer details from invoice
     */
    protected function resolveCustomerDetails(Invoice $invoice): array
    {
        // Check if already set on invoice
        if ($invoice->customer_details) {
            return $invoice->customer_details;
        }

        // Try patient relationship
        if ($invoice->patient && $invoice->patient->user) {
            $user = $invoice->patient->user;

            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ];
        }

        // Try user relationship
        if ($invoice->user) {
            return [
                'name' => $invoice->user->name,
                'email' => $invoice->user->email,
                'phone' => $invoice->user->phone ?? '',
            ];
        }

        return [];
    }

    /**
     * Prepare line items for Razorpay invoice
     */
    protected function prepareLineItems(Invoice $invoice): array
    {
        $items = $invoice->items ?? [];
        $lineItems = [];

        foreach ($items as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;

            $lineItems[] = [
                'name' => $item['name'] ?? 'Item '.($index + 1),
                'description' => $item['description'] ?? '',
                'quantity' => (int) $quantity,
                'amount' => (int) round($quantity * $unitPrice * 100),
                'currency' => $invoice->currency ?? 'INR',
            ];
        }

        // Fallback if no items
        if (empty($lineItems)) {
            $totalAmount = $invoice->total_amount ?? 0;
            $lineItems[] = [
                'name' => 'Service',
                'description' => 'Payment for services',
                'quantity' => 1,
                'amount' => (int) round($totalAmount * 100),
                'currency' => $invoice->currency ?? 'INR',
            ];
        }

        return $lineItems;
    }

    /**
     * Map Razorpay status to local status
     */
    protected function mapStatus(string $razorpayStatus): string
    {
        return match ($razorpayStatus) {
            'draft' => RazorpayInvoice::STATUS_DRAFT,
            'issued' => RazorpayInvoice::STATUS_ISSUED,
            'paid' => RazorpayInvoice::STATUS_PAID,
            'partially_paid' => RazorpayInvoice::STATUS_PARTIALLY_PAID,
            'cancelled' => RazorpayInvoice::STATUS_CANCELLED,
            'expired' => RazorpayInvoice::STATUS_EXPIRED,
            default => RazorpayInvoice::STATUS_DRAFT,
        };
    }

    /**
     * Handle payment captured event from webhook
     */
    public function handlePaymentCaptured(array $payment): void
    {
        $appointment = Appointment::with(['doctor.hospital', 'encounter'])
            ->where('razorpay_order_id', $payment['order_id'] ?? null)
            ->first();

        if (! $appointment) {
            Log::warning('Razorpay: Appointment not found for captured payment', [
                'payment_id' => $payment['id'] ?? null,
                'order_id' => $payment['order_id'] ?? null,
            ]);

            return;
        }

        $appointment->update([
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'payment_id' => $appointment->payment_id ?: ($payment['id'] ?? null),
        ]);

        if (isset($appointment->encounter->id)) {
            try {
                $existingBilling = BillingCore::where('encounter_id', $appointment->encounter->id)
                    ->where('patient_id', $appointment->patient_id)
                    ->where('payment_type', 'razorpay')
                    ->where('reason', 'Appointment Payment')
                    ->first();

                if (! $existingBilling) {
                    BillingCore::create([
                        'encounter_id' => $appointment->encounter->id,
                        'patient_id' => $appointment->patient_id,
                        'hospital_id' => $appointment->doctor->hospital->id,
                        'other_billing_id' => null,
                        'cpt' => null,
                        'cpt_charge' => $appointment->fee_amount ?? 0,
                        'icd_pointer' => null,
                        'unit' => 1,
                        'modifier' => null,
                        'dos_f' => Carbon::now()->format('Y-m-d'),
                        'dos_t' => Carbon::now()->format('Y-m-d'),
                        'billing_group' => null,
                        'payment' => $appointment->fee_amount ?? 0,
                        'reason' => 'Appointment Payment',
                        'payment_type' => 'razorpay',
                        'service_start' => now(),
                        'service_end' => now(),
                    ]);
                }
            } catch (Throwable $th) {
                Log::error('Failed to create billing record from payment captured event', [
                    'payment_id' => $payment['id'] ?? null,
                    'error' => $th->getMessage(),
                ]);
            }
        }

        try {
            $invoicePayload = [
                'patient_id' => $appointment->patient_id,
                'user_id' => $appointment->created_by,
                'doctor_id' => $appointment->doctor_id,
                'hospital_id' => $appointment?->doctor?->hospital?->id,
                'appointment_id' => $appointment->id,
                'lab_order_id' => null,
                'pharmacy_order_id' => null,
                'subscription_id' => null,
                'amount' => $appointment->fee_amount ?? 0,
                'tax_amount' => 0,
                'discount_amount' => $appointment->discount ?? 0,
                'total_amount' => $appointment->total_amount ?? 0,
                'currency' => $appointment->currency ?? 'INR',
                'status' => 'paid',
                'razorpay_invoice_id' => null,
                'razorpay_payment_id' => $payment['id'] ?? null,
                'razorpay_order_id' => $payment['order_id'] ?? null,
                'razorpay_customer_id' => null,
                'payment_method' => 'razorpay',
                'due_date' => now(),
                'paid_at' => now(),
                'sent_at' => now(),
                'viewed_at' => now(),
                'items' => [],
                'customer_details' => [],
                'notes' => '',
                'terms_conditions' => '',
                'created_by' => $appointment->created_by,
                'updated_by' => $appointment->created_by,
            ];

            $existingInvoice = Invoice::where('razorpay_order_id', $payment['order_id'] ?? null)
                ->orWhere('appointment_id', $appointment->id)
                ->first();

            if ($existingInvoice) {
                $existingInvoice->fill($invoicePayload);
                $existingInvoice->save();
            } else {
                for ($attempt = 0; $attempt < 3; $attempt++) {
                    try {
                        Invoice::create([
                            'invoice_number' => Invoice::generateInvoiceNumber(),
                            ...$invoicePayload,
                        ]);
                        break;
                    } catch (QueryException $exception) {
                        $isDuplicateInvoiceNumber = $exception->getCode() === '23000'
                            && str_contains($exception->getMessage(), 'invoices_invoice_number_unique');

                        if (! $isDuplicateInvoiceNumber || $attempt === 2) {
                            throw $exception;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create invoice from payment captured event', [
                'payment_id' => $payment['id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle subscription activated event
     */
    public function handleSubscriptionActivated(array $subscription): void
    {
        $razorpaySubscriptionId = $subscription['id'] ?? null;

        Log::info('Razorpay: Subscription activated', [
            'subscription_id' => $razorpaySubscriptionId,
            'status' => $subscription['status'] ?? null,
        ]);

        $this->syncSubscriptionStateFromWebhook($subscription, 'active');
    }

    public function handleSubscriptionCharged(array $subscription, ?array $invoice = null): void
    {
        Log::info('Razorpay: Subscription charged', [
            'subscription_id' => $subscription['id'] ?? null,
            'paid_count' => $subscription['paid_count'] ?? null,
            'current_start' => $subscription['current_start'] ?? null,
            'current_end' => $subscription['current_end'] ?? null,
        ]);

        $localSubscription = $this->syncSubscriptionStateFromWebhook($subscription, 'active', true);

        if ($localSubscription && $invoice) {
            $this->findOrCreateSubscriptionInvoiceFromWebhook($invoice);
        }
    }

    public function handleSubscriptionCompleted(array $subscription): void
    {
        Log::info('Razorpay: Subscription completed', [
            'subscription_id' => $subscription['id'] ?? null,
        ]);

        $status = ! empty($subscription['current_end']) && Carbon::createFromTimestamp($subscription['current_end'])->isFuture()
            ? 'active'
            : 'expired';

        $this->syncSubscriptionStateFromWebhook($subscription, $status, true);
    }

    public function handleSubscriptionPaused(array $subscription): void
    {
        Log::info('Razorpay: Subscription paused', [
            'subscription_id' => $subscription['id'] ?? null,
        ]);

        $this->syncSubscriptionStateFromWebhook($subscription, 'suspend');
    }

    public function handleSubscriptionResumed(array $subscription): void
    {
        Log::info('Razorpay: Subscription resumed', [
            'subscription_id' => $subscription['id'] ?? null,
        ]);

        $this->syncSubscriptionStateFromWebhook($subscription, 'active');
    }

    /**
     * Handle subscription cancelled event
     */
    public function handleSubscriptionCancelled(array $subscription): void
    {
        $razorpaySubscriptionId = $subscription['id'] ?? null;

        Log::info('Razorpay: Subscription cancelled', [
            'subscription_id' => $razorpaySubscriptionId,
        ]);

        $this->syncSubscriptionStateFromWebhook($subscription, 'cancelled');
    }

    protected function syncSubscriptionStateFromWebhook(array $subscription, string $status, bool $markPaid = false): ?UserSubscription
    {
        $razorpaySubscriptionId = $subscription['id'] ?? null;
        if (! $razorpaySubscriptionId) {
            return null;
        }

        $localSubscription = UserSubscription::with('subscriptionPlan')
            ->where('razorpay_subscription_id', $razorpaySubscriptionId)
            ->first();

        if (! $localSubscription && ! empty(data_get($subscription, 'notes.subscription_id'))) {
            $localSubscription = UserSubscription::with('subscriptionPlan')
                ->where('id', data_get($subscription, 'notes.subscription_id'))
                ->first();

            if ($localSubscription && empty($localSubscription->razorpay_subscription_id)) {
                $localSubscription->update([
                    'razorpay_subscription_id' => $razorpaySubscriptionId,
                ]);
            }
        }

        if (! $localSubscription) {
            Log::warning('Razorpay: Local subscription not found for webhook', [
                'subscription_id' => $razorpaySubscriptionId,
            ]);
            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'Subscription renewal sync failed',
                    'A Razorpay subscription webhook could not be matched to a local subscription.',
                    'subscription_renewal_sync_failed',
                    [
                        'meta' => [
                            'razorpay_subscription_id' => $razorpaySubscriptionId,
                            'status' => $status,
                        ],
                    ]
                )
            );

            return null;
        }

        $updateData = [
            'status' => $status,
        ];

        $preserveExistingTrialWindow = (
            ! $markPaid
            && $localSubscription->status === 'active'
            && ($localSubscription->payment_status ?? null) !== 'paid'
            && ! empty($localSubscription->end_date)
            && ! empty($subscription['current_start'])
            && Carbon::createFromTimestamp($subscription['current_start'])->isFuture()
        );

        if (! empty($subscription['current_start']) && ! $preserveExistingTrialWindow) {
            $updateData['start_date'] = Carbon::createFromTimestamp($subscription['current_start'])->toDateString();
        }

        if (! empty($subscription['current_end']) && ! $preserveExistingTrialWindow) {
            $updateData['end_date'] = Carbon::createFromTimestamp($subscription['current_end'])->toDateString();
        }

        if ($markPaid || ! empty($subscription['paid_count'])) {
            $updateData['payment_status'] = 'paid';
        }

        $localSubscription->update($updateData);

        return $localSubscription->fresh();
    }

    protected function syncSubscriptionRenewalFromInvoiceEntity(array $entity, Invoice $invoice): void
    {
        $razorpaySubscriptionId = $entity['subscription_id'] ?? null;
        if (! $razorpaySubscriptionId) {
            return;
        }

        $subscription = UserSubscription::with('subscriptionPlan')
            ->where('razorpay_subscription_id', $razorpaySubscriptionId)
            ->first();

        if (! $subscription) {
            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'Subscription renewal sync failed',
                    'A Razorpay renewal invoice could not be matched to a local subscription.',
                    'subscription_renewal_sync_failed',
                    [
                        'meta' => [
                            'razorpay_subscription_id' => $razorpaySubscriptionId,
                            'invoice_id' => $invoice->id,
                        ],
                    ]
                )
            );

            return;
        }

        $updateData = [
            'status' => 'active',
            'payment_status' => 'paid',
        ];

        if (! empty($entity['billing_start'])) {
            $updateData['start_date'] = Carbon::createFromTimestamp($entity['billing_start'])->toDateString();
        }

        if (! empty($entity['billing_end'])) {
            $updateData['end_date'] = Carbon::createFromTimestamp($entity['billing_end'])->toDateString();
        } elseif ($subscription->subscriptionPlan && $subscription->end_date) {
            $startDate = Carbon::parse($subscription->end_date);
            $updateData['end_date'] = match (strtolower($subscription->subscriptionPlan->frequency ?? 'monthly')) {
                'yearly' => $startDate->copy()->addYear()->toDateString(),
                default => $startDate->copy()->addMonth()->toDateString(),
            };
        }

        $subscription->update($updateData);

        if ($invoice->subscription_id !== $subscription->id) {
            $invoice->update(['subscription_id' => $subscription->id]);
        }
    }

    protected function mapInvoiceStatusToLocal(string $status): string
    {
        return match ($status) {
            'paid' => Invoice::STATUS_PAID,
            'partially_paid' => Invoice::STATUS_PARTIAL,
            'issued' => Invoice::STATUS_SENT,
            'cancelled' => Invoice::STATUS_CANCELLED,
            'expired' => Invoice::STATUS_OVERDUE,
            default => Invoice::STATUS_DRAFT,
        };
    }
}
