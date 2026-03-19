<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\RazorpayTransaction;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Transaction Service
 * Handles payment verification, refunds, and transaction management
 */
class RazorpayTransactionService
{
    protected $keyId;

    protected $keySecret;

    protected $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key');
        $this->keySecret = config('services.razorpay.secret');
        $this->baseUrl = config('services.razorpay.base_url', 'https://api.razorpay.com/v1');
    }

    /**
     * Verify and process a payment
     */
    public function verifyPayment(array $paymentData): array
    {
        try {
            Log::info('Verifying Razorpay payment', [
                'razorpay_payment_id' => $paymentData['razorpay_payment_id'] ?? null,
                'razorpay_order_id' => $paymentData['razorpay_order_id'] ?? null,
            ]);

            // Validate required fields
            $requiredFields = ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'];
            foreach ($requiredFields as $field) {
                if (empty($paymentData[$field])) {
                    return [
                        'success' => false,
                        'error' => "Missing required field: {$field}",
                    ];
                }
            }

            // Verify signature
            if (! $this->verifySignature($paymentData)) {
                return [
                    'success' => false,
                    'error' => 'Invalid payment signature',
                ];
            }

            // Fetch payment details from Razorpay
            $paymentDetails = $this->fetchPayment($paymentData['razorpay_payment_id']);

            if (! $paymentDetails['success']) {
                return $paymentDetails;
            }

            $payment = $paymentDetails['data'];

            // Check if payment is successful
            if ($payment['status'] !== 'captured') {
                return [
                    'success' => false,
                    'error' => 'Payment not successful',
                    'status' => $payment['status'],
                ];
            }

            // Find and update local order/subscription
            $order = RazorpayTransaction::where('razorpay_order_id', $paymentData['razorpay_order_id'])->first();

            if ($order) {
                // Create transaction record
                $transaction = $this->createTransactionRecord($order, $payment);

                // Update subscription if applicable
                if ($order->subscription_id) {
                    $this->activateSubscription($order->subscription_id, $payment);
                }

                // Update invoice if applicable
                if ($order->invoice_id) {
                    $this->markInvoicePaid($order->invoice_id, $payment);
                }

                Log::info('Payment verified and processed successfully', [
                    'transaction_id' => $transaction->id ?? null,
                    'razorpay_payment_id' => $payment['id'],
                ]);

                return [
                    'success' => true,
                    'transaction' => $transaction ?? null,
                    'payment' => $payment,
                ];
            }

            return [
                'success' => false,
                'error' => 'Order not found',
            ];

        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a refund for a payment
     */
    public function createRefund(string $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);

            $refundData = [
                'payment_id' => $paymentId,
                'notes' => [
                    'reason' => $reason ?? 'Refund requested',
                ],
            ];

            if ($amount) {
                $refundData['amount'] = (int) round($amount * 100); // Convert to paise
            }

            $refund = $api->refund->create($refundData);

            Log::info('Refund created', [
                'refund_id' => $refund['id'],
                'payment_id' => $paymentId,
                'amount' => $refund['amount'] / 100,
            ]);

            return [
                'success' => true,
                'data' => [
                    'refund_id' => $refund['id'],
                    'amount' => $refund['amount'] / 100,
                    'status' => $refund['status'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Refund creation failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch payment details from Razorpay
     */
    public function fetchPayment(string $paymentId): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            $payment = $api->payment->fetch($paymentId);

            return [
                'success' => true,
                'data' => $payment->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to fetch payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature
     */
    public function verifySignature(array $paymentData): bool
    {
        try {
            $payload = $paymentData['razorpay_order_id'].'|'.$paymentData['razorpay_payment_id'];
            $expectedSignature = hash_hmac('sha256', $payload, $this->keySecret);

            return hash_equals($expectedSignature, $paymentData['razorpay_signature'] ?? '');
        } catch (\Exception $e) {
            Log::error('Signature verification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create transaction record
     */
    protected function createTransactionRecord(RazorpayTransaction $order, array $payment): RazorpayTransaction
    {
        return RazorpayTransaction::create([
            'razorpay_order_id' => $order->razorpay_order_id,
            'razorpay_payment_id' => $payment['id'],
            'razorpay_refund_id' => null,
            'user_id' => $order->user_id,
            'patient_id' => $order->patient_id,
            'invoice_id' => $order->invoice_id,
            'subscription_id' => $order->subscription_id,
            'amount' => $payment['amount'] / 100,
            'currency' => strtoupper($payment['currency']),
            'status' => $this->mapPaymentStatus($payment['status']),
            'method' => $payment['method'] ?? null,
            'card_type' => $payment['card'] ?? null,
            'card_last4' => $payment['card']['last4'] ?? null,
            'bank' => $payment['bank'] ?? null,
            'wallet' => $payment['wallet'] ?? null,
            'vpa' => $payment['vpa'] ?? null,
            'fee' => ($payment['fee'] ?? 0) / 100,
            'tax' => ($payment['tax'] ?? 0) / 100,
            'notes' => $payment['notes'] ?? [],
            'captured_at' => isset($payment['captured_at']) ? date('Y-m-d H:i:s', $payment['captured_at']) : now(),
        ]);
    }

    /**
     * Activate subscription after successful payment
     */
    protected function activateSubscription(string $subscriptionId, array $payment): void
    {
        try {
            $subscription = UserSubscription::find($subscriptionId);

            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'payment_status' => 'paid',
                    'razorpay_payment_id' => $payment['id'],
                    'razorpay_order_id' => $payment['order_id'] ?? null,
                ]);

                // Activate user
                if ($subscription->user) {
                    $subscription->user->update([
                        'is_active' => true,
                    ]);
                }

                Log::info('Subscription activated', [
                    'subscription_id' => $subscriptionId,
                    'payment_id' => $payment['id'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to activate subscription', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark invoice as paid
     */
    protected function markInvoicePaid(string $invoiceId, array $payment): void
    {
        try {
            $invoice = Invoice::find($invoiceId);

            if ($invoice) {
                $invoice->update([
                    'status' => Invoice::STATUS_PAID,
                    'razorpay_payment_id' => $payment['id'],
                    'razorpay_order_id' => $payment['order_id'] ?? null,
                    'payment_method' => $payment['method'] ?? null,
                    'paid_at' => now(),
                ]);

                Log::info('Invoice marked as paid', [
                    'invoice_id' => $invoiceId,
                    'payment_id' => $payment['id'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to mark invoice as paid', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map Razorpay payment status to our status
     */
    protected function mapPaymentStatus(string $razorpayStatus): string
    {
        $statusMap = [
            'created' => RazorpayTransaction::STATUS_CREATED,
            'pending' => RazorpayTransaction::STATUS_PENDING,
            'captured' => RazorpayTransaction::STATUS_CAPTURED,
            'refunded' => RazorpayTransaction::STATUS_REFUNDED,
            'failed' => RazorpayTransaction::STATUS_FAILED,
        ];

        return $statusMap[$razorpayStatus] ?? RazorpayTransaction::STATUS_PENDING;
    }

    /**
     * Get transactions summary
     */
    public function getSummary(array $filters = []): array
    {
        $query = RazorpayTransaction::query();

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['subscription_id'])) {
            $query->where('subscription_id', $filters['subscription_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'successful_transactions' => (clone $query)->where('status', RazorpayTransaction::STATUS_CAPTURED)->count(),
            'successful_amount' => (clone $query)->where('status', RazorpayTransaction::STATUS_CAPTURED)->sum('amount'),
            'refunded_transactions' => (clone $query)->where('status', RazorpayTransaction::STATUS_REFUNDED)->count(),
            'refunded_amount' => (clone $query)->where('status', RazorpayTransaction::STATUS_REFUNDED)->sum('amount'),
        ];
    }

    /**
     * Test Razorpay connection
     */
    public function testConnection(): array
    {
        try {
            if (empty($this->keyId) || empty($this->keySecret)) {
                return [
                    'success' => false,
                    'error' => 'Razorpay credentials not configured',
                ];
            }

            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            $api->payment->all(['count' => 1]);

            return [
                'success' => true,
                'message' => 'Razorpay connection successful',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
