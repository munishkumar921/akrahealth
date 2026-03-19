<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\UserSubscription;
use App\Services\RazorpayInvoiceService;
use App\Services\RazorpayOrderService;
use App\Services\RazorpayTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

/**
 * Razorpay Payment Controller
 * Handles all Razorpay-related operations including orders, invoices, and webhooks
 */
class RazorpayController extends Controller
{
    protected $invoiceService;

    protected $orderService;

    protected $transactionService;

    public function __construct(
        RazorpayInvoiceService $invoiceService,
        RazorpayOrderService $orderService,
        RazorpayTransactionService $transactionService
    ) {
        $this->invoiceService = $invoiceService;
        $this->orderService = $orderService;
        $this->transactionService = $transactionService;
    }

    /**
     * Test Razorpay connection and configuration
     */
    public function testConnection(): JsonResponse
    {
        $results = [
            'invoice' => $this->invoiceService->testConnection(),
            'order' => $this->orderService->testConnection(),
            'transaction' => $this->transactionService->testConnection(),
        ];

        $allSuccessful = collect($results)->every(fn ($r) => $r['success'] ?? false);

        return Response::json([
            'success' => $allSuccessful,
            'results' => $results,
        ], $allSuccessful ? 200 : 400);
    }

    /**
     * Create Razorpay order for subscription
     */
    public function createSubscriptionOrder(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => 'required|uuid|exists:user_subscriptions,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
        ]);

        $subscription = UserSubscription::findOrFail($request->subscription_id);
        $amount = $request->amount;
        $currency = $request->currency ?? $subscription->currency ?? 'INR';

        $result = $this->orderService->createSubscriptionOrder($subscription, $amount, $currency);

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
                'error_code' => $result['error_code'] ?? null,
            ], 400);
        }

        return Response::json([
            'success' => true,
            'order' => $result['data'],
            'local_order' => $result['order'] ?? null,
        ]);
    }

    /**
     * Create Razorpay order for invoice payment
     */
    public function createInvoiceOrder(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
            'currency' => 'nullable|string|size:3',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        $currency = $request->currency ?? $invoice->currency ?? 'INR';

        $result = $this->orderService->createInvoiceOrder($invoice, $currency);

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return Response::json([
            'success' => true,
            'order' => $result['data'],
        ]);
    }

    /**
     * Verify payment and activate subscription/invoice
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'subscription_id' => 'nullable|uuid|exists:user_subscriptions,id',
            'invoice_id' => 'nullable|uuid|exists:invoices,id',
        ]);

        $result = $this->transactionService->verifyPayment($request->all());

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        Log::info('Payment verified successfully', [
            'transaction_id' => $result['transaction']->id ?? null,
            'razorpay_payment_id' => $request->razorpay_payment_id,
        ]);

        return Response::json([
            'success' => true,
            'transaction' => $result['transaction'] ?? null,
            'message' => 'Payment verified and processed successfully',
        ]);
    }

    /**
     * Create Razorpay invoice
     */
    public function createInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $result = $this->invoiceService->createInvoice($invoice);

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
                'error_code' => $result['error_code'] ?? null,
            ], 400);
        }

        return Response::json([
            'success' => true,
            'razorpay_invoice_id' => $result['razorpay_invoice_id'],
            'invoice_data' => $result['data'],
        ]);
    }

    /**
     * Send invoice to customer
     */
    public function sendInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $result = $this->invoiceService->sendInvoice($invoice);

        return Response::json([
            'success' => $result['success'],
            'message' => $result['message'] ?? $result['error'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Send invoice reminder
     */
    public function sendInvoiceReminder(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $result = $this->invoiceService->sendReminder($invoice);

        return Response::json([
            'success' => $result['success'],
            'message' => $result['message'] ?? $result['error'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $result = $this->invoiceService->cancelInvoice($invoice);

        return Response::json([
            'success' => $result['success'],
            'message' => $result['message'] ?? $result['error'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Get invoice details
     */
    public function getInvoice(string $razorpayInvoiceId): JsonResponse
    {
        $result = $this->invoiceService->getInvoice($razorpayInvoiceId);

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return Response::json([
            'success' => true,
            'invoice' => $result['data'],
        ]);
    }

    /**
     * Get order details
     */
    public function getOrder(string $orderId): JsonResponse
    {
        $result = $this->orderService->fetchOrder($orderId);

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return Response::json([
            'success' => true,
            'order' => $result['data'],
        ]);
    }

    /**
     * Get payment details
     */
    public function getPayment(string $paymentId): JsonResponse
    {
        $result = $this->transactionService->fetchPayment($paymentId);

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return Response::json([
            'success' => true,
            'payment' => $result['data'],
        ]);
    }

    /**
     * Create refund
     */
    public function createRefund(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|string',
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        $result = $this->transactionService->createRefund(
            $request->payment_id,
            $request->amount,
            $request->reason
        );

        if (! $result['success']) {
            return Response::json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        Log::info('Refund created', [
            'refund_id' => $result['data']['refund_id'] ?? null,
            'payment_id' => $request->payment_id,
        ]);

        return Response::json([
            'success' => true,
            'refund' => $result['data'],
        ]);
    }

    /**
     * Debug invoice creation
     */
    public function debugInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|uuid|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $debugInfo = $this->invoiceService->debugInvoiceCreation($invoice);

        return Response::json([
            'success' => true,
            'debug' => $debugInfo,
        ]);
    }

    /**
     * Get transactions summary
     */
    public function getTransactionsSummary(Request $request): JsonResponse
    {
        $filters = [
            'user_id' => $request->user_id,
            'subscription_id' => $request->subscription_id,
            'status' => $request->status,
        ];

        $summary = $this->transactionService->getSummary($filters);

        return Response::json([
            'success' => true,
            'summary' => $summary,
        ]);
    }
}
