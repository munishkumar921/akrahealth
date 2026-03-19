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
        Schema::create('razorpay_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Razorpay-specific fields
            $table->string('razorpay_invoice_id')->unique();
            $table->string('razorpay_customer_id')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();

            // Invoice reference
            $table->string('invoice_number')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('patient_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('subscription_id')->nullable();

            // Invoice details
            $table->string('status')->default('draft'); // draft, issued, paid, partially_paid, cancelled, expired
            $table->string('type')->default('invoice'); // invoice, receipt
            $table->string('currency', 10)->default('INR');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);

            // Customer details (JSON)
            $table->json('customer_details')->nullable();

            // Line items (JSON)
            $table->json('line_items')->nullable();

            // Payment details
            $table->string('payment_method')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('short_url')->nullable();
            $table->string('pdf_url')->nullable();

            // Dates
            $table->timestamp('invoice_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            // Webhook tracking
            $table->timestamp('last_webhook_received_at')->nullable();
            $table->json('last_webhook_payload')->nullable();

            // Additional info
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('razorpay_invoice_id');
            $table->index('razorpay_customer_id');
            $table->index('status');
            $table->index('invoice_id');
            $table->index('invoice_number');
            $table->index(['invoice_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_invoices');
    }
};
