<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayPaymentService
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
     * Create a payment link for subscription
     *
     * @return array
     */
    public function createPaymentLink(array $data)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/payment_links", [
                    'amount' => $data['amount'] * 100, // Convert to paisa
                    'currency' => $data['currency'],
                    'accept_partial' => false,
                    'first_min_partial_amount' => 0,
                    'expire_by' => strtotime('+24 hours'),
                    'reference_id' => $data['reference_id'],
                    'description' => $data['description'],
                    'customer' => [
                        'name' => $data['customer']['name'],
                        'email' => $data['customer']['email'],
                        'contact' => $data['customer']['contact'],
                    ],
                    'notify' => [
                        'sms' => true,
                        'email' => true,
                    ],
                    'reminder_enable' => true,
                    'notes' => $data['notes'] ?? [],
                    'callback_url' => $data['callback_url'],
                    'callback_method' => 'get',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Razorpay payment link creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay payment link creation exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch payment link details
     *
     * @return array
     */
    public function fetchPaymentLink(string $paymentLinkId)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/payment_links/{$paymentLinkId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature
     *
     * @return bool
     */
    public function verifyPaymentSignature(array $data)
    {
        $expectedSignature = hash_hmac('sha256', $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'], $this->keySecret);

        return hash_equals($expectedSignature, $data['razorpay_signature']);
    }

    /**
     * Create a Razorpay order
     *
     * @return array
     */
    public function createOrder(array $data)
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);

            // Determine currency subunit multiplier
            $currency = strtoupper($data['currency'] ?? 'INR');
            $multiplier = $this->getSubunitMultiplier($currency);
            $amount = (int) round($data['amount'] * $multiplier);

            // if ($amount <= 0) {
            //     return [
            //         'success' => false,
            //         'error' => 'Invalid amount',
            //     ];
            // }

            // Razorpay receipt field has max 40 characters
            $receipt = $data['receipt'] ?? 'order_'.time();
            if (strlen($receipt) > 40) {
                $receipt = substr(md5($receipt), 0, 36);
            }

            $orderData = [
                'receipt' => $receipt,
                'amount' => $amount,
                'currency' => $currency,
                'payment_capture' => 1,
                'notes' => $data['notes'] ?? [],
            ];

            $razorpayOrder = $api->order->create($orderData);

            if (! $razorpayOrder || ! isset($razorpayOrder['id'])) {
                Log::error('Razorpay order creation returned invalid response', [
                    'response' => $razorpayOrder,
                ]);

                return [
                    'success' => false,
                    'error' => 'Invalid response from payment gateway',
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                ],
            ];

        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            Log::error('Razorpay BadRequestError', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => 'Invalid payment request: '.$e->getMessage(),
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
     * Get currency subunit multiplier
     * Razorpay uses currency subunits; default is 100 for most currencies.
     * Zero-decimal currencies should use multiplier 1.
     */
    private function getSubunitMultiplier(string $currency): int
    {
        $zeroDecimal = ['JPY', 'KRW', 'VND'];

        return in_array($currency, $zeroDecimal, true) ? 1 : 100;
    }
}
