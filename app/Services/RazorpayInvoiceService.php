<?php

namespace App\Services;

use App\Mail\RazorpayInvoiceMail;
use App\Models\Appointment;
use App\Models\BillingCore;
use App\Models\Invoice;
use App\Models\RazorpayInvoice;
use App\Models\Transaction;
use Carbon\Carbon;
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
            Log::warning('Razorpay: Invoice not found', ['razorpay_invoice_id' => $razorpayInvoiceId]);

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
        Transaction::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'user_id' => $invoice->user_id,
            'payment_type' => 'razorpay',
            'amount' => $amount ?? $invoice->total_amount,
            'currency' => $invoice->currency,
            'status' => 'completed',
            'transaction_id' => $paymentId,
            'payment_method' => $method ?? 'razorpay',
            'notes' => 'Payment via Razorpay: '.$invoice->invoice_number,
        ]);
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
                'razorpay_payment_id' => $invoice->razorpay_payment_id,
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
        $appointment = Appointment::with(['doctor.hospital', 'encounter'])->where('razorpay_order_id', $payment['order_id'])->firstOrFail();

        $appointment->update([
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'payment_id' => $payment['id'] ?? null,
        ]);

        if (isset($appointment->encounter->id)) {

            try {
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
            } catch (Throwable $th) {
                Log::error('Failed to create billing record from payment captured event', [
                    'payment_id' => $payment['id'] ?? null,
                    'error' => $th->getMessage(),
                ]);
            }
        }

        $lastInvoice = Invoice::latest()->first();
        if ($lastInvoice) {
            $invoice_number = $lastInvoice->invoice_number + 1;
        } else {
            $invoice_number = 1000001;
        }

        try {
            Invoice::updateOrCreate(
                [
                    'razorpay_order_id' => $payment['order_id'],
                ],
                [
                    'invoice_number' => $invoice_number,
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
                ]
            );
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
        $customerId = $subscription['customer_id'] ?? null;
        $planId = $subscription['plan_id'] ?? null;

        Log::info('Razorpay: Subscription activated', [
            'subscription_id' => $razorpaySubscriptionId,
            'customer_id' => $customerId,
            'plan_id' => $planId,
        ]);

        // Find and update local subscription
        $localSubscription = \App\Models\UserSubscription::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if ($localSubscription) {
            $localSubscription->update([
                'status' => 'active',
                'razorpay_customer_id' => $customerId,
                'started_at' => now(),
            ]);
        }
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

        // Find and update local subscription
        $localSubscription = \App\Models\UserSubscription::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if ($localSubscription) {
            $localSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }
    }
}
