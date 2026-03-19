<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\RazorpayOrder;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

/**
 * Razorpay Order Service
 * Handles creation and management of Razorpay orders for payments
 */
class RazorpayOrderService
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
     * Create a Razorpay order for subscription
     */
    public function createSubscriptionOrder(UserSubscription $subscription, float $amount, string $currency = 'INR'): array
    {
        try {
            $user = $subscription->user;

            Log::info('Creating Razorpay order for subscription', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            // Validate credentials
            if (empty($this->keyId) || empty($this->keySecret)) {
                return [
                    'success' => false,
                    'error' => 'Razorpay credentials not configured',
                ];
            }

            // Prepare receipt and notes
            $receipt = 'sub_'.$subscription->id;
            if (strlen($receipt) > 40) {
                $receipt = substr(md5($receipt), 0, 40);
            }

            // Determine currency subunit multiplier
            $multiplier = $this->getSubunitMultiplier($currency);
            $amountInSubunits = (int) round($amount * $multiplier);

            if ($amountInSubunits < 100 && $currency === 'INR') {
                return [
                    'success' => false,
                    'error' => 'Amount must be at least ₹1.00',
                ];
            }

            // Create Razorpay order using SDK
            $api = new Api($this->keyId, $this->keySecret);

            $orderData = [
                'receipt' => $receipt,
                'amount' => $amountInSubunits,
                'currency' => strtoupper($currency),
                'payment_capture' => 1, // Auto-capture payments
                'notes' => [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
            ];

            $razorpayOrder = $api->order->create($orderData);

            if (! $razorpayOrder || ! isset($razorpayOrder['id'])) {
                Log::error('Razorpay order creation failed - invalid response', [
                    'response' => $razorpayOrder,
                ]);

                return [
                    'success' => false,
                    'error' => 'Invalid response from payment gateway',
                ];
            }

            // Create local RazorpayOrder record
            $localOrder = $this->createLocalOrderRecord($subscription, $razorpayOrder, $amount, $currency);

            Log::info('Razorpay order created successfully', [
                'order_id' => $razorpayOrder['id'],
                'subscription_id' => $subscription->id,
            ]);

            return [
                'success' => true,
                'data' => [
                    'id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                    'status' => $razorpayOrder['status'],
                ],
                'order' => $localOrder,
            ];

        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            Log::error('Razorpay BadRequestError', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => 'Invalid payment request: '.$e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        } catch (\Razorpay\Api\Errors\ServerError $e) {
            Log::error('Razorpay ServerError', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Payment gateway server error',
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
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
     * Create a Razorpay order for invoice payment
     */
    public function createInvoiceOrder(Invoice $invoice, string $currency = 'INR'): array
    {
        try {
            $user = $invoice->user ?? $invoice->patient?->user;

            Log::info('Creating Razorpay order for invoice', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->total_amount,
            ]);

            $receipt = 'inv_'.$invoice->invoice_number;
            if (strlen($receipt) > 40) {
                $receipt = substr(md5($receipt), 0, 40);
            }

            $multiplier = $this->getSubunitMultiplier($currency);
            $amountInSubunits = (int) round($invoice->total_amount * $multiplier);

            $api = new Api($this->keyId, $this->keySecret);

            $orderData = [
                'receipt' => $receipt,
                'amount' => $amountInSubunits,
                'currency' => strtoupper($currency),
                'payment_capture' => 1,
                'notes' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'email' => $user->email ?? '',
                ],
            ];

            $razorpayOrder = $api->order->create($orderData);

            // Create local record
            $localOrder = RazorpayOrder::create([
                'order_id' => $razorpayOrder['id'],
                'invoice_id' => $invoice->id,
                'user_id' => $user->id ?? null,
                'patient_id' => $invoice->patient_id ?? null,
                'subscription_id' => $invoice->subscription_id ?? null,
                'amount' => $razorpayOrder['amount'] / $multiplier,
                'currency' => $razorpayOrder['currency'],
                'status' => $razorpayOrder['status'],
                'receipt' => $razorpayOrder['receipt'],
                'notes' => $orderData['notes'],
            ]);

            return [
                'success' => true,
                'data' => [
                    'id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create invoice order', [
                'invoice_id' => $invoice->id,
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
            $api = new Api($this->keyId, $this->keySecret);

            $attributes = [
                'razorpay_order_id' => $paymentData['razorpay_order_id'],
                'razorpay_payment_id' => $paymentData['razorpay_payment_id'],
                'razorpay_signature' => $paymentData['razorpay_signature'],
            ];

            $api->utility->verifyPaymentSignature($attributes);

            return true;

        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Payment signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Signature verification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Fetch order details from Razorpay
     */
    public function fetchOrder(string $orderId): array
    {
        try {
            $api = new Api($this->keyId, $this->keySecret);
            $order = $api->order->fetch($orderId);

            return [
                'success' => true,
                'data' => $order->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to fetch Razorpay order', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create local RazorpayOrder record
     *
     * @param  RazorpayOrder  $razorpayOrder
     */
    protected function createLocalOrderRecord(UserSubscription $subscription, $razorpayOrder, float $amount, string $currency): RazorpayOrder
    {
        $multiplier = $this->getSubunitMultiplier($currency);

        return RazorpayOrder::create([
            'order_id' => $razorpayOrder['id'],
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'amount' => $razorpayOrder['amount'] / $multiplier,
            'currency' => $razorpayOrder['currency'],
            'status' => $razorpayOrder['status'],
            'receipt' => $razorpayOrder['receipt'] ?? null,
            'attempts' => $razorpayOrder['attempts'] ?? 0,
            'notes' => $razorpayOrder['notes'] ?? [],
        ]);
    }

    /**
     * Update order status after payment
     */
    public function updateOrderStatus(string $orderId, string $paymentId, string $status): ?RazorpayOrder
    {
        $order = RazorpayOrder::where('order_id', $orderId)->first();

        if (! $order) {
            Log::warning('Razorpay order not found for update', ['order_id' => $orderId]);

            return null;
        }

        $order->update([
            'status' => $status,
            'razorpay_payment_id' => $paymentId,
            'paid_at' => now(),
        ]);

        Log::info('Razorpay order status updated', [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'status' => $status,
        ]);

        return $order;
    }

    /**
     * Get currency subunit multiplier
     */
    private function getSubunitMultiplier(string $currency): int
    {
        $zeroDecimal = ['JPY', 'KRW', 'VND'];

        return in_array(strtoupper($currency), $zeroDecimal, true) ? 1 : 100;
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

            $api = new Api($this->keyId, $this->keySecret);
            // Try to fetch a dummy payment to test credentials
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
