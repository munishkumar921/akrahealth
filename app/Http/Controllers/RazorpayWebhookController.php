<?php

namespace App\Http\Controllers;

use App\Services\InAppNotificationService;
use App\Services\RazorpayInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Webhook Controller
 *
 * POST /razorpay/webhook : main Razorpay webhook endpoint
 *
 * Events handled:
 *
 * payment.captured
 * payment.failed
 * subscription.activated
 * subscription.charged
 * subscription.completed
 * subscription.paused
 * subscription.resumed
 * subscription.cancelled
 * invoice.paid
 * invoice.partially_paid
 * invoice.viewed
 * invoice.cancelled
 * invoice.expired
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

        /* Verify signature */
        if (! $this->verifySignature($payload, $signature, $secret)) {
            Log::warning('Razorpay: Invalid signature', [
                'ip' => $request->ip(),
            ]);
            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'Payment webhook failure',
                    'Razorpay webhook signature verification failed.',
                    'payment_webhook_failure',
                    [
                        'meta' => [
                            'ip' => $request->ip(),
                            'event' => (string) $request->input('event', ''),
                        ],
                    ]
                )
            );

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = (string) $request->input('event', '');
        $data = $request->input('payload', []);

        try {
            match ($event) {
                /* Payment events */
                'payment.captured' => $this->handlePaymentCaptured($data['payment']['entity'] ?? []),
                'payment.failed' => $this->handlePaymentFailed($data['payment']['entity'] ?? []),

                // Subscription events
                'subscription.activated' => $this->handleSubscriptionActivated($data['subscription']['entity'] ?? []),
                'subscription.charged' => $this->handleSubscriptionCharged(
                    $data['subscription']['entity'] ?? [],
                    $data['invoice']['entity'] ?? null
                ),
                'subscription.completed' => $this->handleSubscriptionCompleted($data['subscription']['entity'] ?? []),
                'subscription.paused' => $this->handleSubscriptionPaused($data['subscription']['entity'] ?? []),
                'subscription.resumed' => $this->handleSubscriptionResumed($data['subscription']['entity'] ?? []),
                'subscription.cancelled' => $this->handleSubscriptionCancelled($data['subscription']['entity'] ?? []),

                // Invoice events
                'invoice.paid' => $this->handleInvoicePaid($data['invoice']['entity'] ?? []),
                'invoice.partially_paid' => $this->handleInvoicePartiallyPaid($data['invoice']['entity'] ?? []),
                'invoice.viewed' => $this->handleInvoiceViewed($data['invoice']['entity'] ?? []),
                'invoice.cancelled' => $this->handleInvoiceCancelled($data['invoice']['entity'] ?? []),
                'invoice.expired' => $this->handleInvoiceExpired($data['invoice']['entity'] ?? []),

                default => Log::info('Razorpay: Unhandled event', ['event' => $event]),
            };
        } catch (\Throwable $exception) {
            Log::error('Razorpay: Webhook processing failed', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'Payment webhook failure',
                    'Razorpay webhook processing failed for event '.$event.'.',
                    'payment_webhook_failure',
                    [
                        'meta' => [
                            'event' => $event,
                            'error' => $exception->getMessage(),
                        ],
                    ]
                )
            );

            return response()->json(['status' => 'accepted'], 200);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function handleInvoiceWebhook(Request $request): JsonResponse
    {
        return $this->handle($request);
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature(string $payload, ?string $signature, ?string $secret): bool
    {
        if ($payload === '' || empty($signature) || empty($secret)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured(array $payment): void
    {
        if (! empty($payment['id']) && ! empty($payment['order_id'])) {
            $this->razorpayInvoiceService->handlePaymentCaptured($payment);
        } else {
            Log::warning('Razorpay: payment.captured missing required identifiers', [
                'payment_id' => $payment['id'] ?? null,
                'order_id' => $payment['order_id'] ?? null,
            ]);
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

        $notificationService = app(InAppNotificationService::class);
        $notificationService->notifySuperAdmins(
            $notificationService->buildPayload(
                'Payment failed for subscription',
                'A Razorpay payment failed for order '.($payment['order_id'] ?? 'unknown').'.',
                'payment_failed',
                [
                    'meta' => [
                        'payment_id' => $payment['id'] ?? null,
                        'order_id' => $payment['order_id'] ?? null,
                        'error_code' => $payment['error_code'] ?? null,
                    ],
                ]
            )
        );

        $cacheKey = 'superadmin:payment_failed_spike:'.now()->toDateString();
        $failuresToday = Cache::increment($cacheKey);
        Cache::put($cacheKey, $failuresToday, now()->endOfDay());

        if ($failuresToday === 5) {
            $notificationService->notifySuperAdmins(
                $notificationService->buildPayload(
                    'Failed payment spike detected',
                    'Five payment failures have been detected today. Review the payment gateway immediately.',
                    'payment_failed_spike',
                    [
                        'meta' => [
                            'failures_today' => $failuresToday,
                            'date' => now()->toDateString(),
                        ],
                    ]
                )
            );
        }
    }

    /**
     * Handle subscription activated event
     */
    private function handleSubscriptionActivated(array $subscription): void
    {
        if (empty($subscription['id'])) {
            Log::warning('Razorpay: subscription.activated missing subscription id');

            return;
        }

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
        if (empty($subscription['id'])) {
            Log::warning('Razorpay: subscription.cancelled missing subscription id');

            return;
        }

        Log::info('Razorpay: Subscription cancelled', [
            'subscription_id' => $subscription['id'] ?? null,
        ]);

        $this->razorpayInvoiceService->handleSubscriptionCancelled($subscription);
    }

    private function handleSubscriptionCharged(array $subscription, ?array $invoice = null): void
    {
        if (empty($subscription['id'])) {
            Log::warning('Razorpay: subscription.charged missing subscription id');

            return;
        }

        $this->razorpayInvoiceService->handleSubscriptionCharged($subscription, $invoice);
    }

    private function handleSubscriptionCompleted(array $subscription): void
    {
        if (empty($subscription['id'])) {
            Log::warning('Razorpay: subscription.completed missing subscription id');

            return;
        }

        $this->razorpayInvoiceService->handleSubscriptionCompleted($subscription);
    }

    private function handleSubscriptionPaused(array $subscription): void
    {
        if (empty($subscription['id'])) {
            Log::warning('Razorpay: subscription.paused missing subscription id');

            return;
        }

        $this->razorpayInvoiceService->handleSubscriptionPaused($subscription);
    }

    private function handleSubscriptionResumed(array $subscription): void
    {
        if (empty($subscription['id'])) {
            Log::warning('Razorpay: subscription.resumed missing subscription id');

            return;
        }

        $this->razorpayInvoiceService->handleSubscriptionResumed($subscription);
    }

    /**
     * Handle invoice paid event
     */
    private function handleInvoicePaid(array $invoice): void
    {
        if (empty($invoice['id'])) {
            Log::warning('Razorpay: invoice.paid missing invoice id');

            return;
        }

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
        if (empty($invoice['id'])) {
            Log::warning('Razorpay: invoice.partially_paid missing invoice id');

            return;
        }

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
        if (empty($invoice['id'])) {
            Log::warning('Razorpay: invoice.viewed missing invoice id');

            return;
        }

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
        if (empty($invoice['id'])) {
            Log::warning('Razorpay: invoice.cancelled missing invoice id');

            return;
        }

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
        if (empty($invoice['id'])) {
            Log::warning('Razorpay: invoice.expired missing invoice id');

            return;
        }

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
