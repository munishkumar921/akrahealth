<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_platforms', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Platform identification
            $table->string('name'); // e.g., 'Razorpay', 'Stripe', 'PayPal'
            $table->string('code')->unique(); // e.g., 'razorpay', 'stripe', 'paypal'
            $table->text('description')->nullable();

            // Credentials (encrypted in production)
            $table->string('api_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('merchant_id')->nullable()->comment('For gateways like Razorpay');
            $table->string('webhook_url')->nullable();

            // Platform settings
            $table->string('environment')->default('sandbox')->comment('sandbox or live');
            $table->json('settings')->nullable()->comment('Additional configuration');
            $table->json('supported_currencies')->nullable()->comment('List of supported currency codes');

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_platforms');
    }
};
