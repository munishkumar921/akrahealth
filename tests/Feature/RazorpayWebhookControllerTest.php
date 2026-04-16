<?php

namespace Tests\Feature;

use App\Services\RazorpayInvoiceService;
use Mockery;
use Tests\TestCase;

class RazorpayWebhookControllerTest extends TestCase
{
    /**
     * setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.razorpay.webhook_secret', 'test_webhook_secret');
    }

    /**
     * test_rejects_webhook_with_invalid_signature
     *
     * @return void
     */
    public function test_rejects_webhook_with_invalid_signature(): void
    {
        $response = $this->postWebhook('/razorpay/webhook', [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test_invalid',
                        'order_id' => 'order_test_invalid',
                    ],
                ],
            ],
        ], 'invalid-signature');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid signature']);
    }

    /**
     * @dataProvider supportedWebhookEventsProvider
     */
    public function test_webhook_endpoint_handles_supported_events(
        string $event,
        array $payload,
        array $serviceExpectations
    ): void {
        $this->bindWebhookServiceMock($serviceExpectations);

        $response = $this->postWebhook('/razorpay/webhook', [
            'event' => $event,
            'payload' => $payload,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    /**
     * test_invoice_webhook_alias_uses_same_handler
     *
     * @return void
     */
    public function test_invoice_webhook_alias_uses_same_handler(): void
    {
        $this->bindWebhookServiceMock([
            [
                'method' => 'handleWebhook',
                'times' => 1,
                'with' => function (array $payload): bool {
                    return $payload['event'] === 'invoice.paid'
                        && ($payload['payload']['invoice']['entity']['id'] ?? null) === 'inv_alias_001';
                },
            ],
        ]);

        $response = $this->postWebhook('/api/razorpay/invoice-webhook', [
            'event' => 'invoice.paid',
            'payload' => [
                'invoice' => [
                    'entity' => [
                        'id' => 'inv_alias_001',
                        'status' => 'paid',
                    ],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    /**
     * test_unhandled_event_returns_ok_without_service_side_effects
     *
     * @return void
     */
    public function test_unhandled_event_returns_ok_without_service_side_effects(): void
    {
        $this->bindWebhookServiceMock([]);

        $response = $this->postWebhook('/razorpay/webhook', [
            'event' => 'payment.authorized',
            'payload' => [],
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    /**
     * supportedWebhookEventsProvider
     *
     * @return array
     */
    public static function supportedWebhookEventsProvider(): array
    {
        return [
            'payment.captured' => [
                'payment.captured',
                [
                    'payment' => [
                        'entity' => [
                            'id' => 'pay_capture_001',
                            'order_id' => 'order_capture_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handlePaymentCaptured',
                        'times' => 1,
                        'with' => fn(array $payment): bool => $payment['id'] === 'pay_capture_001'
                            && $payment['order_id'] === 'order_capture_001',
                    ],
                ],
            ],
            'payment.failed' => [
                'payment.failed',
                [
                    'payment' => [
                        'entity' => [
                            'id' => 'pay_failed_001',
                            'order_id' => 'order_failed_001',
                        ],
                    ],
                ],
                [],
            ],
            'subscription.activated' => [
                'subscription.activated',
                [
                    'subscription' => [
                        'entity' => [
                            'id' => 'sub_active_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleSubscriptionActivated',
                        'times' => 1,
                        'with' => fn(array $subscription): bool => $subscription['id'] === 'sub_active_001',
                    ],
                ],
            ],
            'subscription.charged' => [
                'subscription.charged',
                [
                    'subscription' => [
                        'entity' => [
                            'id' => 'sub_charged_001',
                            'current_start' => 1777593600,
                            'current_end' => 1780272000,
                        ],
                    ],
                    'invoice' => [
                        'entity' => [
                            'id' => 'inv_charged_001',
                            'subscription_id' => 'sub_charged_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleSubscriptionCharged',
                        'times' => 1,
                        'with' => function (array $subscription, ?array $invoice): bool {
                            return $subscription['id'] === 'sub_charged_001'
                                && ($invoice['id'] ?? null) === 'inv_charged_001';
                        },
                    ],
                ],
            ],
            'subscription.completed' => [
                'subscription.completed',
                [
                    'subscription' => [
                        'entity' => [
                            'id' => 'sub_completed_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleSubscriptionCompleted',
                        'times' => 1,
                        'with' => fn(array $subscription): bool => $subscription['id'] === 'sub_completed_001',
                    ],
                ],
            ],
            'subscription.paused' => [
                'subscription.paused',
                [
                    'subscription' => [
                        'entity' => [
                            'id' => 'sub_paused_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleSubscriptionPaused',
                        'times' => 1,
                        'with' => fn(array $subscription): bool => $subscription['id'] === 'sub_paused_001',
                    ],
                ],
            ],
            'subscription.resumed' => [
                'subscription.resumed',
                [
                    'subscription' => [
                        'entity' => [
                            'id' => 'sub_resumed_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleSubscriptionResumed',
                        'times' => 1,
                        'with' => fn(array $subscription): bool => $subscription['id'] === 'sub_resumed_001',
                    ],
                ],
            ],
            'subscription.cancelled' => [
                'subscription.cancelled',
                [
                    'subscription' => [
                        'entity' => [
                            'id' => 'sub_cancelled_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleSubscriptionCancelled',
                        'times' => 1,
                        'with' => fn(array $subscription): bool => $subscription['id'] === 'sub_cancelled_001',
                    ],
                ],
            ],
            'invoice.paid' => [
                'invoice.paid',
                [
                    'invoice' => [
                        'entity' => [
                            'id' => 'inv_paid_001',
                            'status' => 'paid',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleWebhook',
                        'times' => 1,
                        'with' => fn(array $payload): bool => $payload['event'] === 'invoice.paid'
                            && ($payload['payload']['invoice']['entity']['id'] ?? null) === 'inv_paid_001',
                    ],
                ],
            ],
            'invoice.partially_paid' => [
                'invoice.partially_paid',
                [
                    'invoice' => [
                        'entity' => [
                            'id' => 'inv_partial_001',
                            'status' => 'partially_paid',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleWebhook',
                        'times' => 1,
                        'with' => fn(array $payload): bool => $payload['event'] === 'invoice.partially_paid'
                            && ($payload['payload']['invoice']['entity']['id'] ?? null) === 'inv_partial_001',
                    ],
                ],
            ],
            'invoice.viewed' => [
                'invoice.viewed',
                [
                    'invoice' => [
                        'entity' => [
                            'id' => 'inv_viewed_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleWebhook',
                        'times' => 1,
                        'with' => fn(array $payload): bool => $payload['event'] === 'invoice.viewed'
                            && ($payload['payload']['invoice']['entity']['id'] ?? null) === 'inv_viewed_001',
                    ],
                ],
            ],
            'invoice.cancelled' => [
                'invoice.cancelled',
                [
                    'invoice' => [
                        'entity' => [
                            'id' => 'inv_cancelled_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleWebhook',
                        'times' => 1,
                        'with' => fn(array $payload): bool => $payload['event'] === 'invoice.cancelled'
                            && ($payload['payload']['invoice']['entity']['id'] ?? null) === 'inv_cancelled_001',
                    ],
                ],
            ],
            'invoice.expired' => [
                'invoice.expired',
                [
                    'invoice' => [
                        'entity' => [
                            'id' => 'inv_expired_001',
                        ],
                    ],
                ],
                [
                    [
                        'method' => 'handleWebhook',
                        'times' => 1,
                        'with' => fn(array $payload): bool => $payload['event'] === 'invoice.expired'
                            && ($payload['payload']['invoice']['entity']['id'] ?? null) === 'inv_expired_001',
                    ],
                ],
            ],
        ];
    }

    /**
     * bindWebhookServiceMock
     *
     * @param  mixed $expectations
     * @return void
     */
    private function bindWebhookServiceMock(array $expectations): void
    {
        $mock = Mockery::mock(RazorpayInvoiceService::class);

        foreach ($expectations as $expectation) {
            $call = $mock->shouldReceive($expectation['method'])
                ->times($expectation['times']);

            if (isset($expectation['with'])) {
                $call->withArgs($expectation['with']);
            }
        }

        $this->app->instance(RazorpayInvoiceService::class, $mock);
    }

    /**
     * postWebhook
     *
     * @param  mixed $uri
     * @param  mixed $payload
     * @param  mixed $signature
     * @return void
     */
    private function postWebhook(string $uri, array $payload, ?string $signature = null)
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature ??= hash_hmac('sha256', $body, (string) config('services.razorpay.webhook_secret'));

        return $this->call(
            'POST',
            $uri,
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            ],
            $body
        );
    }
}
