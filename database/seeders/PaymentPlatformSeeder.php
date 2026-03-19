<?php

namespace Database\Seeders;

use App\Models\PaymentPlatform;
use Illuminate\Database\Seeder;

class PaymentPlatformSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Razorpay',
                'code' => 'razorpay',
                'description' => 'India\'s first payment gateway with instant refund, fast checkout, and supports all payment modes.',
                'environment' => 'sandbox',
                'supported_currencies' => ['INR', 'USD'],
                'is_active' => true,
                'is_default' => true,
                'settings' => [
                    'timeout' => 30,
                    'retry_enabled' => true,
                    'retry_count' => 3,
                ],
            ],
            [
                'name' => 'Stripe',
                'code' => 'stripe',
                'description' => 'A complete payments platform with powerful APIs for web and mobile applications.',
                'environment' => 'sandbox',
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD'],
                'is_active' => false,
                'is_default' => false,
                'settings' => [
                    'timeout' => 30,
                    'retry_enabled' => true,
                    'retry_count' => 3,
                    'webhook_secret' => null,
                ],
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'description' => 'Accept payments online with PayPal, the world\'s leading payment processor.',
                'environment' => 'sandbox',
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY'],
                'is_active' => false,
                'is_default' => false,
                'settings' => [
                    'timeout' => 30,
                    'brand_name' => 'AkraHealth',
                    'landing_page' => 'login',
                ],
            ],
        ];

        foreach ($platforms as $platform) {
            PaymentPlatform::create($platform);
        }

        $this->command->info('Payment platforms seeded successfully!');
    }
}
