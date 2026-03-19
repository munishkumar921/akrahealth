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
        Schema::create('razorpay_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id')->unique();
            $table->string('razorpay_payment_id')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('patient_id')->nullable();
            $table->uuid('subscription_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status')->default('created');
            $table->string('receipt')->nullable();
            $table->integer('attempts')->default(0);
            $table->json('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('subscription_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_orders');
    }
};
