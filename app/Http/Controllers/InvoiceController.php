<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices (Admin).
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');

        $query = Invoice::with(['patient.user', 'user', 'doctor.user', 'hospital'])
            ->when($keyword, function ($q) use ($keyword) {
                $q->search($keyword);
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            })
            ->latest();

        $invoices = $query->paginate('per_page', 5)->withQueryString();

        // Transform for frontend
        $invoices->getCollection()->transform(function ($invoice) {
            return $this->transformInvoice($invoice);
        });

        return Inertia::render('Admin/Invoice/InvoiceList', [
            'invoices' => $invoices,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Display invoices for patient.
     */
    public function patientInvoices(Request $request)
    {
        $user = auth()->user();
        $patient = $user->patient;

        if (! $patient) {
            return redirect()->back()->with('error', 'Patient profile not found.');
        }

        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');

        $query = Invoice::with(['doctor.user', 'hospital'])
            ->where('patient_id', $patient->id)
            ->when($keyword, function ($q) use ($keyword) {
                $q->search($keyword);
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest();

        $invoices = $query->paginate($this->paginate)->withQueryString();

        $invoices->getCollection()->transform(function ($invoice) {
            return $this->transformInvoice($invoice);
        });

        return Inertia::render('Patient/Invoices', [
            'invoices' => $invoices,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show invoice details.
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'patient.user',
            'user',
            'doctor.user',
            'hospital',
            'appointment',
            'labOrder',
            'pharmacyOrder',
            'subscription.subscriptionPlan',
            'transactions',
        ])->findOrFail($id);

        return Inertia::render('Admin/Invoice/InvoiceShow', [
            'invoice' => $this->transformInvoice($invoice),
        ]);
    }

    /**
     * Show invoice for patient.
     */
    public function patientInvoiceShow($id)
    {
        $user = auth()->user();
        $patient = $user->patient;

        $invoice = Invoice::with([
            'doctor.user',
            'hospital',
            'transactions',
        ])
            ->where('patient_id', $patient->id)
            ->findOrFail($id);

        // Mark as viewed
        if (! $invoice->viewed_at) {
            $invoice->markAsViewed();
        }

        return Inertia::render('Patient/InvoiceShow', [
            'invoice' => $this->transformInvoice($invoice),
        ]);
    }

    /**
     * Create a new invoice.
     */
    public function create(Request $request)
    {
        $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'type' => 'required|string|in:appointment,lab_order,pharmacy_order,subscription,manual',
            'reference_id' => 'required_if:type,appointment,lab_order,pharmacy_order,subscription|exists_uuid',
        ]);

        $invoice = null;

        switch ($request->type) {
            case 'appointment':
                $appointment = \App\Models\Appointment::findOrFail($request->reference_id);
                $invoice = $this->invoiceService->createAppointmentInvoice($appointment);
                break;

            case 'lab_order':
                $labOrder = \App\Models\LabOrder::findOrFail($request->reference_id);
                $invoice = $this->invoiceService->createLabOrderInvoice($labOrder);
                break;

            case 'pharmacy_order':
                $pharmacyOrder = \App\Models\PharmacyOrder::findOrFail($request->reference_id);
                $invoice = $this->invoiceService->createPharmacyOrderInvoice($pharmacyOrder);
                break;

            case 'subscription':
                $subscription = \App\Models\UserSubscription::findOrFail($request->reference_id);
                $invoice = $this->invoiceService->createSubscriptionInvoice($subscription);
                break;

            case 'manual':
                $invoice = $this->invoiceService->createInvoice($request->all());
                break;
        }

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Store a manually created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'user_id' => 'nullable|exists:users,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'hospital_id' => 'nullable|exists:hospitals,id',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $invoice = $this->invoiceService->createInvoice($validated);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Update invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if (! in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_SENT])) {
            return redirect()->back()->with('error', 'Cannot edit invoice in current status.');
        }

        $validated = $request->validate([
            'items' => 'sometimes|array',
            'items.*.name' => 'sometimes|string',
            'items.*.quantity' => 'sometimes|numeric|min:1',
            'items.*.unit_price' => 'sometimes|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        if (isset($validated['items'])) {
            $invoice->items = $validated['items'];
            $invoice->calculateTotals();
        }

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Send invoice to customer.
     */
    public function send(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if (! in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_SENT])) {
            return redirect()->back()->with('error', 'Cannot send invoice in current status.');
        }

        $result = $this->invoiceService->sendInvoice($invoice);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Invoice sent successfully.');
        }

        return redirect()->back()->with('error', 'Failed to send invoice: '.($result['error'] ?? 'Unknown error'));
    }

    /**
     * Send reminder for invoice.
     */
    public function sendReminder(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if (! in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_VIEWED, Invoice::STATUS_OVERDUE])) {
            return redirect()->back()->with('error', 'Cannot send reminder for this invoice.');
        }

        $result = $this->invoiceService->sendReminder($invoice);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Reminder sent successfully.');
        }

        return redirect()->back()->with('error', 'Failed to send reminder: '.($result['error'] ?? 'Unknown error'));
    }

    /**
     * Cancel invoice.
     */
    public function cancel(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === Invoice::STATUS_PAID) {
            return redirect()->back()->with('error', 'Cannot cancel a paid invoice.');
        }

        $result = $this->invoiceService->cancelInvoice($invoice);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Invoice cancelled successfully.');
        }

        return redirect()->back()->with('error', 'Failed to cancel invoice.');
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf($id)
    {
        try {
            $invoice = Invoice::with([
                'patient.user',
                'user',
                'doctor.user',
                'hospital',
                'subscription.subscriptionPlan',
            ])->findOrFail($id);

            $data = [
                'invoice' => $invoice,
                'invoice_number' => $invoice->invoice_number,
                'date' => $invoice->created_at->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'patient' => $invoice->patient,
                'patientUser' => $invoice->patient?->user,
                'doctor' => $invoice->doctor,
                'doctorUser' => $invoice->doctor?->user,
                'hospital' => $invoice->hospital,
                'subscription' => $invoice->subscription,
                'plan' => $invoice->subscription?->subscriptionPlan,
                'transactions' => $invoice->transactions,
            ];

            $pdf = Pdf::loadView('pdf.invoice_new', $data);

            return $pdf->download('invoice-'.$invoice->invoice_number.'.pdf');

        } catch (\Exception $e) {
            Log::error('Invoice PDF download error', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to generate invoice PDF.');
        }
    }

    /**
     * Process manual payment.
     */
    public function processPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validated['amount'] > $invoice->getUnpaidAmountAttribute()) {
            return redirect()->back()->with('error', 'Payment amount cannot exceed unpaid amount.');
        }

        $transaction = $this->invoiceService->addPayment($invoice, $validated);

        return redirect()->back()->with('success', 'Payment processed successfully.');
    }

    /**
     * Get status summary for dashboard.
     */
    public function getSummary()
    {
        $summary = $this->invoiceService->getSummary();

        return response()->json($summary);
    }

    /**
     * Transform invoice for frontend.
     */
    protected function transformInvoice(Invoice $invoice): array
    {
        // Get patient name (from patient or user relation)
        $patientName = null;
        if ($invoice->patient) {
            $patientName = $invoice->patient->name ?? ($invoice->patient->user->name ?? 'N/A');
        } elseif ($invoice->user) {
            $patientName = $invoice->user->name;
        }

        // Get plan name from subscription
        $planName = null;
        if ($invoice->subscription && $invoice->subscription->subscriptionPlan) {
            $planName = $invoice->subscription->subscriptionPlan->title ?? $invoice->subscription->subscriptionPlan->name ?? 'Subscription';
        }

        // Get doctor name
        $doctorName = null;
        if ($invoice->doctor) {
            $doctorName = $invoice->doctor->user->name ?? ('Dr. '.($invoice->doctor->first_name ?? '').' '.($invoice->doctor->last_name ?? ''));
        }

        // Get hospital name
        $hospitalName = null;
        if ($invoice->hospital) {
            $hospitalName = $invoice->hospital->name;
        }

        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'patient_id' => $invoice->patient_id,
            'patient_name' => $patientName,
            'user_id' => $invoice->user_id,
            'user_name' => $invoice->user ? $invoice->user->name : null,
            'doctor_id' => $invoice->doctor_id,
            'doctor_name' => $doctorName,
            'hospital_id' => $invoice->hospital_id,
            'hospital_name' => $hospitalName,
            'subscription_id' => $invoice->subscription_id,
            'plan_name' => $planName,
            'amount' => $invoice->amount,
            'tax_amount' => $invoice->tax_amount,
            'discount_amount' => $invoice->discount_amount,
            'total_amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'status' => $invoice->status,
            'status_label' => $invoice->formatted_status,
            'razorpay_invoice_id' => $invoice->razorpay_invoice_id,
            'razorpay_payment_id' => $invoice->razorpay_payment_id,
            'razorpay_order_id' => $invoice->razorpay_order_id,
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
            'sent_at' => $invoice->sent_at?->format('Y-m-d H:i:s'),
            'viewed_at' => $invoice->viewed_at?->format('Y-m-d H:i:s'),
            'items_count' => count($invoice->items ?? []),
            'paid_amount' => $invoice->transactions()->where('status', 'completed')->sum('amount'),
            'unpaid_amount' => $invoice->getUnpaidAmountAttribute(),
            'payment_progress' => $invoice->getPaymentProgressAttribute(),
            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $invoice->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get status options for dropdown.
     */
    protected function getStatusOptions(): array
    {
        return [
            ['value' => '', 'label' => 'All Status'],
            ['value' => Invoice::STATUS_DRAFT, 'label' => 'Draft'],
            ['value' => Invoice::STATUS_SENT, 'label' => 'Sent'],
            ['value' => Invoice::STATUS_VIEWED, 'label' => 'Viewed'],
            ['value' => Invoice::STATUS_PARTIAL, 'label' => 'Partial'],
            ['value' => Invoice::STATUS_PAID, 'label' => 'Paid'],
            ['value' => Invoice::STATUS_OVERDUE, 'label' => 'Overdue'],
            ['value' => Invoice::STATUS_CANCELLED, 'label' => 'Cancelled'],
        ];
    }
}
