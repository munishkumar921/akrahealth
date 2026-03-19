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
        Schema::create('razorpay_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->unique();
            $table->string('razorpay_refund_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('patient_id')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('subscription_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status')->default('pending');
            $table->string('method')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_last4')->nullable();
            $table->string('bank')->nullable();
            $table->string('wallet')->nullable();
            $table->string('vpa')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->json('notes')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('razorpay_order_id');
            $table->index('razorpay_payment_id');
            $table->index('subscription_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_transactions');
    }
};
