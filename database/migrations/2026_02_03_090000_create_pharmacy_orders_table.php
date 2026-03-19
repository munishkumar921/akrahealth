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
        Schema::create('pharmacy_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('pharmacy_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('doctor_id')->constrained()->onDelete('cascade');

            $table->enum('status', [
                'pending',
                'accepted',
                'processing',
                'ready',
                'dispensed',
                'completed',
                'cancelled',
                'rejected',
            ])->default('pending');

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);

            $table->json('medications')->nullable();
            $table->text('notes')->nullable();
            $table->text('delivery_address')->nullable();

            $table->string('prescription_file')->nullable();
            $table->boolean('delivery_required')->default(false);
            $table->string('delivery_status', 50)->nullable();

            $table->dateTime('scheduled_at')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_orders');
    }
};
