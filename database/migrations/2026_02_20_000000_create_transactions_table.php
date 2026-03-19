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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /* Foreign keys */
            $table->foreignUuid('lab_order_id')->nullable()->constrained('lab_orders')->onDelete('set null');
            $table->foreignUuid('pharmacy_order_id')->nullable()->constrained('pharmacy_orders')->onDelete('set null');
            $table->foreignUuid('patient_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            /* Payment info */
            $table->string('payment_type')->nullable(); // e.g., 'cash', 'card', 'upi', 'insurance', 'online'
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->string('transaction_id')->nullable(); // External payment gateway transaction ID
            $table->string('payment_method')->nullable(); // Details of payment method
            $table->text('notes')->nullable();

            /* Timestamps */
            $table->softDeletes();
            $table->timestamps();

            /* Indexes for faster queries */
            $table->index('status');
            $table->index('payment_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
