<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    protected $razorpayInvoiceService;

    public function __construct(RazorpayInvoiceService $razorpayInvoiceService)
    {
        $this->razorpayInvoiceService = $razorpayInvoiceService;
    }

    /**
     * Create a new invoice
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // Generate invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();

            // Prepare items
            $items = $data['items'] ?? [];

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'patient_id' => $data['patient_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'hospital_id' => $data['hospital_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'lab_order_id' => $data['lab_order_id'] ?? null,
                'pharmacy_order_id' => $data['pharmacy_order_id'] ?? null,
                'subscription_id' => $data['subscription_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $data['total_amount'] ?? 0,
                'currency' => $data['currency'] ?? 'INR',
                'status' => $data['status'] ?? Invoice::STATUS_DRAFT,
                'due_date' => $data['due_date'] ?? null,
                'items' => $items,
                'customer_details' => $data['customer_details'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'created_by' => auth()->id() ?? null,
            ]);

            // If total_amount not provided, calculate from items
            if (empty($data['total_amount']) && ! empty($items)) {
                $invoice->calculateTotals();
            }

            Log::info('Invoice created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
            ]);

            return $invoice;
        });
    }

    /**
     * Create invoice for appointment
     */
    public function createAppointmentInvoice(\App\Models\Appointment $appointment): Invoice
    {
        $patient = $appointment->patient;
        $doctor = $appointment->doctor;

        $items = [
            [
                'name' => 'Consultation Fee',
                'description' => 'Appointment consultation on '.$appointment->appointment_date,
                'quantity' => 1,
                'unit_price' => $appointment->fee_amount ?? 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ],
        ];

        return $this->createInvoice([
            'patient_id' => $patient?->id,
            'user_id' => $patient?->user_id,
            'doctor_id' => $doctor?->id,
            'hospital_id' => $doctor?->hospital_id ?? $appointment->hospital_id,
            'appointment_id' => $appointment->id,
            'items' => $items,
            'currency' => $appointment->currency ?? 'INR',
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Appointment ID: '.$appointment->id,
        ]);
    }

    /**
     * Create invoice for lab order
     */
    public function createLabOrderInvoice(\App\Models\LabOrder $labOrder): Invoice
    {
        $appointment = $labOrder->appointment;
        $patient = $appointment?->patient;
        $doctor = $labOrder->doctor;

        // Build items from lab tests
        $items = [];
        $tests = is_array($labOrder->tests) ? $labOrder->tests : [];

        foreach ($tests as $test) {
            $items[] = [
                'name' => $test['name'] ?? 'Lab Test',
                'description' => $test['description'] ?? '',
                'quantity' => 1,
                'unit_price' => $test['price'] ?? 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ];
        }

        return $this->createInvoice([
            'patient_id' => $patient?->id,
            'user_id' => $patient?->user_id,
            'doctor_id' => $doctor?->id,
            'hospital_id' => $doctor?->hospital_id,
            'appointment_id' => $appointment?->id,
            'lab_order_id' => $labOrder->id,
            'items' => $items,
            'total_amount' => $labOrder->total_amount ?? 0,
            'currency' => 'INR',
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Lab Order ID: '.$labOrder->id,
        ]);
    }

    /**
     * Create invoice for pharmacy order
     */
    public function createPharmacyOrderInvoice(\App\Models\PharmacyOrder $pharmacyOrder): Invoice
    {
        $appointment = $pharmacyOrder->appointment;
        $patient = $appointment?->patient;
        $doctor = $pharmacyOrder->doctor;

        // Build items from medications
        $items = [];
        $medications = is_array($pharmacyOrder->medications) ? $pharmacyOrder->medications : [];

        foreach ($medications as $med) {
            $items[] = [
                'name' => $med['name'] ?? 'Medicine',
                'description' => $med['dosage'] ?? '',
                'quantity' => $med['quantity'] ?? 1,
                'unit_price' => $med['price'] ?? 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ];
        }

        return $this->createInvoice([
            'patient_id' => $patient?->id,
            'user_id' => $patient?->user_id,
            'doctor_id' => $doctor?->id,
            'hospital_id' => $doctor?->hospital_id,
            'appointment_id' => $appointment?->id,
            'pharmacy_order_id' => $pharmacyOrder->id,
            'items' => $items,
            'total_amount' => $pharmacyOrder->total_amount ?? 0,
            'currency' => 'INR',
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Pharmacy Order ID: '.$pharmacyOrder->id,
        ]);
    }

    /**
     * Create invoice for subscription
     */
    public function createSubscriptionInvoice(\App\Models\UserSubscription $subscription): Invoice
    {
        $user = $subscription->user;
        $plan = $subscription->subscriptionPlan;

        $items = [
            [
                'name' => $plan->title ?? 'Subscription Plan',
                'description' => $plan->description ?? 'Subscription payment',
                'quantity' => 1,
                'unit_price' => $subscription->amount ?? 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ],
        ];

        return $this->createInvoice([
            'user_id' => $user?->id,
            'subscription_id' => $subscription->id,
            'items' => $items,
            'total_amount' => $subscription->amount ?? 0,
            'currency' => $subscription->currency ?? 'INR',
            'due_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Subscription ID: '.$subscription->id,
        ]);
    }

    /**
     * Send invoice to customer
     */
    public function sendInvoice(Invoice $invoice): array
    {
        return $this->razorpayInvoiceService->sendInvoice($invoice);
    }

    /**
     * Send reminder for invoice
     */
    public function sendReminder(Invoice $invoice): array
    {
        return $this->razorpayInvoiceService->sendReminder($invoice);
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(Invoice $invoice): array
    {
        return $this->razorpayInvoiceService->cancelInvoice($invoice);
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Invoice $invoice, string $status): Invoice
    {
        $invoice->update(['status' => $status]);

        if ($status === Invoice::STATUS_PAID) {
            $invoice->markAsPaid();
        } elseif ($status === Invoice::STATUS_OVERDUE) {
            $invoice->markAsOverdue();
        }

        return $invoice;
    }

    /**
     * Add payment to invoice
     */
    public function addPayment(Invoice $invoice, array $paymentData): \App\Models\Transaction
    {
        $transaction = new \App\Models\Transaction([
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'user_id' => $invoice->user_id,
            'payment_type' => $paymentData['payment_type'] ?? 'manual',
            'amount' => $paymentData['amount'],
            'currency' => $invoice->currency,
            'status' => 'completed',
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'payment_method' => $paymentData['payment_method'] ?? null,
            'notes' => $paymentData['notes'] ?? 'Manual payment',
        ]);

        $transaction->save();

        // Check if fully paid
        $paidAmount = $invoice->transactions()->where('status', 'completed')->sum('amount');

        if ($paidAmount >= $invoice->total_amount) {
            $invoice->markAsPaid($paymentData['transaction_id'] ?? null, $paymentData['payment_method'] ?? null);
        } elseif ($paidAmount > 0) {
            $invoice->update(['status' => Invoice::STATUS_PARTIAL]);
        }

        return $transaction;
    }

    /**
     * Get invoices summary for dashboard
     */
    public function getSummary(array $filters = []): array
    {
        $query = Invoice::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_invoices' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'paid_invoices' => (clone $query)->paid()->count(),
            'paid_amount' => (clone $query)->paid()->sum('total_amount'),
            'unpaid_invoices' => (clone $query)->unpaid()->count(),
            'unpaid_amount' => (clone $query)->unpaid()->sum('total_amount'),
            'overdue_invoices' => (clone $query)->overdue()->count(),
            'overdue_amount' => (clone $query)->overdue()->sum('total_amount'),
        ];
    }

    /**
     * Get invoice statistics for charts
     */
    public function getStatistics(string $period = 'month'): array
    {
        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        $invoices = Invoice::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $invoices->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->toArray(),
            'counts' => $invoices->pluck('count')->toArray(),
            'amounts' => $invoices->pluck('amount')->toArray(),
        ];
    }

    /**
     * Duplicate invoice
     */
    public function duplicate(Invoice $invoice): Invoice
    {
        return $this->createInvoice([
            'patient_id' => $invoice->patient_id,
            'user_id' => $invoice->user_id,
            'doctor_id' => $invoice->doctor_id,
            'hospital_id' => $invoice->hospital_id,
            'items' => $invoice->items,
            'currency' => $invoice->currency,
            'notes' => $invoice->notes,
            'terms_conditions' => $invoice->terms_conditions,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);
    }
}
