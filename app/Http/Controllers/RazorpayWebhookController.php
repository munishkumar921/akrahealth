<?php

namespace App\Http\Controllers;

use App\Services\RazorpayInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Webhook Controller
 *
 * Handles Razorpay webhook events:
 * - payment.captured, payment.failed
 * - subscription.activated, subscription.cancelled
 * - invoice.paid, invoice.partially_paid, invoice.viewed, invoice.cancelled, invoice.expired
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayInvoiceService $razorpayInvoiceService
    ) {}

    /**
     * Handle all Razorpay webhooks
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $secret = config('services.razorpay.webhook_secret');

        // Verify signature
        if (! $this->verifySignature($payload, $signature, $secret)) {
            Log::warning('Razorpay: Invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->event;
        $data = $request->payload;

        // Log::info('Razorpay webhook received', [
        //     'event' => $event,
        //     'payload' => $data,
        // ]);

        match ($event) {
            // Payment events
            'payment.captured' => $this->handlePaymentCaptured($data['payment']['entity']),
            'payment.failed' => $this->handlePaymentFailed($data['payment']['entity']),

            // Subscription events
            // 'subscription.activated' => $this->handleSubscriptionActivated($data['subscription']['entity']),
            // 'subscription.cancelled' => $this->handleSubscriptionCancelled($data['subscription']['entity']),

            // Invoice events
            // 'invoice.paid' => $this->handleInvoicePaid($data['invoice']['entity']),
            // 'invoice.partially_paid' => $this->handleInvoicePartiallyPaid($data['invoice']['entity']),
            // 'invoice.viewed' => $this->handleInvoiceViewed($data['invoice']['entity']),
            // 'invoice.cancelled' => $this->handleInvoiceCancelled($data['invoice']['entity']),
            // 'invoice.expired' => $this->handleInvoiceExpired($data['invoice']['entity']),

            default => Log::info('Razorpay: Unhandled event', ['event' => $event]),
        };

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature(string $payload, ?string $signature, ?string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured(array $payment): void
    {
        if (! empty($payment['id'])) {
            $this->razorpayInvoiceService->handlePaymentCaptured($payment);
        }
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed(array $payment): void
    {
        Log::info('Razorpay: Payment failed', [
            'payment_id' => $payment['id'] ?? null,
            'order_id' => $payment['order_id'] ?? null,
            'error_code' => $payment['error_code'] ?? null,
        ]);
    }

    /**
     * Handle subscription activated event
     */
    private function handleSubscriptionActivated(array $subscription): void
    {
        Log::info('Razorpay: Subscription activated', [
            'subscription_id' => $subscription['id'] ?? null,
            'customer_id' => $subscription['customer_id'] ?? null,
        ]);

        $this->razorpayInvoiceService->handleSubscriptionActivated($subscription);
    }

    /**
     * Handle subscription cancelled event
     */
    private function handleSubscriptionCancelled(array $subscription): void
    {
        Log::info('Razorpay: Subscription cancelled', [
            'subscription_id' => $subscription['id'] ?? null,
        ]);

        $this->razorpayInvoiceService->handleSubscriptionCancelled($subscription);
    }

    /**
     * Handle invoice paid event
     */
    private function handleInvoicePaid(array $invoice): void
    {
        Log::info('Razorpay: Invoice paid', [
            'invoice_id' => $invoice['id'] ?? null,
            'amount_paid' => $invoice['amount_paid'] ?? null,
        ]);

        $this->razorpayInvoiceService->handleWebhook([
            'event' => 'invoice.paid',
            'payload' => ['invoice' => ['entity' => $invoice]],
        ]);
    }

    /**
     * Handle invoice partially paid event
     */
    private function handleInvoicePartiallyPaid(array $invoice): void
    {
        Log::info('Razorpay: Invoice partially paid', [
            'invoice_id' => $invoice['id'] ?? null,
            'amount_paid' => $invoice['amount_paid'] ?? null,
        ]);

        $this->razorpayInvoiceService->handleWebhook([
            'event' => 'invoice.partially_paid',
            'payload' => ['invoice' => ['entity' => $invoice]],
        ]);
    }

    /**
     * Handle invoice viewed event
     */
    private function handleInvoiceViewed(array $invoice): void
    {
        $this->razorpayInvoiceService->handleWebhook([
            'event' => 'invoice.viewed',
            'payload' => ['invoice' => ['entity' => $invoice]],
        ]);
    }

    /**
     * Handle invoice cancelled event
     */
    private function handleInvoiceCancelled(array $invoice): void
    {
        $this->razorpayInvoiceService->handleWebhook([
            'event' => 'invoice.cancelled',
            'payload' => ['invoice' => ['entity' => $invoice]],
        ]);
    }

    /**
     * Handle invoice expired event
     */
    private function handleInvoiceExpired(array $invoice): void
    {
        $this->razorpayInvoiceService->handleWebhook([
            'event' => 'invoice.expired',
            'payload' => ['invoice' => ['entity' => $invoice]],
        ]);
    }

    /**
     * Debug endpoint to test webhook configuration
     */
    public function debug(Request $request): JsonResponse
    {
        return response()->json([
            'webhook_secret_configured' => ! empty(config('services.razorpay.webhook_secret')),
            'key_configured' => ! empty(config('services.razorpay.key')),
            'secret_configured' => ! empty(config('services.razorpay.secret')),
            'base_url' => config('services.razorpay.base_url'),
            'environment' => app()->environment(),
            'request_ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
