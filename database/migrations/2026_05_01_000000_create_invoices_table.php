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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /* Invoice Details */
            $table->string('invoice_number')->unique();
            $table->string('status')->default('draft');

            /* Foreign Keys */
            $table->foreignUuid('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->foreignUuid('hospital_id')->nullable()->constrained('hospitals')->onDelete('set null');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignUuid('lab_order_id')->nullable()->constrained('lab_orders')->onDelete('set null');
            $table->foreignUuid('pharmacy_order_id')->nullable()->constrained('pharmacy_orders')->onDelete('set null');
            $table->foreignUuid('subscription_id')->nullable()->constrained('user_subscriptions')->onDelete('set null');

            /* Financial Details */
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('INR');

            /* Razorpay Integration */
            $table->string('razorpay_invoice_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_customer_id')->nullable();
            $table->string('payment_method')->nullable();

            /* Dates */
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();

            /* Items and Details (JSON) */
            $table->json('items')->nullable();
            $table->json('customer_details')->nullable();

            /* Notes and Terms */
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();

            /* Audit Fields */
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            /* Indexes */
            $table->index('status');
            $table->index('invoice_number');
            $table->index('patient_id');
            $table->index('user_id');
            $table->index('due_date');
            $table->index('created_at');
            $table->index('razorpay_invoice_id');
        });

        /* Update transactions table to add invoice relationship and Razorpay fields */
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('razorpay_invoice_id')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['invoice_id', 'razorpay_invoice_id']);
        });

        Schema::dropIfExists('invoices');
    }
};
