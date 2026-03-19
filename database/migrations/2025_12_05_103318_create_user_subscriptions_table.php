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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('subscription_plan_id');
            $table->enum('status', ['pending', 'active', 'suspend', 'reject'])->default('pending');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('razorpay_subscription_id')->nullable();
            $table->string('payment_link_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('currency')->default('INR');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
