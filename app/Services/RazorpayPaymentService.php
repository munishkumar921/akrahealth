<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Cache;
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
     * Create or reuse a Razorpay plan for recurring subscriptions.
     */
    public function createOrGetSubscriptionPlan(SubscriptionPlan $plan): array
    {
        $currency = strtoupper($plan->currency ?? 'INR');
        $multiplier = $this->getSubunitMultiplier($currency);
        $cacheKey = sprintf(
            'razorpay_plan:%s:%s:%s:%s',
            $plan->id,
            (string) $plan->price,
            $currency,
            strtolower($plan->frequency ?? 'monthly')
        );

        if ($cachedPlanId = Cache::get($cacheKey)) {
            return [
                'success' => true,
                'data' => ['id' => $cachedPlanId],
            ];
        }

        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);

            $period = $this->mapPlanFrequencyToPeriod($plan->frequency);
            $amount = (int) round(((float) $plan->price) * $multiplier);

            $razorpayPlan = $api->plan->create([
                'period' => $period,
                'interval' => 1,
                'item' => [
                    'name' => $plan->title ?? 'Subscription Plan',
                    'description' => $plan->label ?? ($plan->title ?? 'Subscription Plan'),
                    'amount' => $amount,
                    'currency' => $currency,
                ],
                'notes' => [
                    'subscription_plan_id' => (string) $plan->id,
                    'plan_frequency' => strtolower($plan->frequency ?? 'monthly'),
                ],
            ]);

            Cache::forever($cacheKey, $razorpayPlan['id']);

            return [
                'success' => true,
                'data' => [
                    'id' => $razorpayPlan['id'],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay plan creation failed', [
                'subscription_plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a delayed-start Razorpay subscription for mandate authorization.
     */
    public function createSubscription(array $data): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);

            $subscriptionData = [
                'plan_id' => $data['plan_id'],
                'total_count' => $data['total_count'] ?? 120,
                'quantity' => 1,
                'customer_notify' => 1,
                'start_at' => $data['start_at'],
                'expire_by' => $data['expire_by'] ?? strtotime('+1 day'),
                'notes' => $data['notes'] ?? [],
            ];

            $razorpaySubscription = $api->subscription->create($subscriptionData);

            return [
                'success' => true,
                'data' => [
                    'id' => $razorpaySubscription['id'],
                    'status' => $razorpaySubscription['status'] ?? null,
                    'short_url' => $razorpaySubscription['short_url'] ?? null,
                ],
            ];
        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            Log::error('Razorpay subscription creation failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => 'Invalid subscription request: '.$e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay subscription creation exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch a Razorpay subscription by id.
     */
    public function fetchSubscription(string $subscriptionId): array
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/subscriptions/{$subscriptionId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Razorpay subscription fetch failed', [
                'subscription_id' => $subscriptionId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay subscription fetch exception', [
                'subscription_id' => $subscriptionId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Razorpay subscription authorization signature.
     */
    public function verifySubscriptionSignature(array $data): bool
    {
        $expectedSignature = hash_hmac(
            'sha256',
            $data['razorpay_payment_id'].'|'.$data['razorpay_subscription_id'],
            $this->keySecret
        );

        return hash_equals($expectedSignature, $data['razorpay_signature']);
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

    private function mapPlanFrequencyToPeriod(?string $frequency): string
    {
        return match (strtolower($frequency ?? 'monthly')) {
            'yearly', 'annual', 'annually' => 'yearly',
            default => 'monthly',
        };
    }
}
