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
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /* Foreign keys */
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignUuid('lab_id')->nullable()->constrained('labs')->onDelete('set null');
            $table->foreignUuid('pharmacy_id')->nullable()->constrained('pharmacies')->onDelete('set null');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            /* Appointment Details */
            $table->string('appointment_type')->nullable();
            $table->enum('appointment_mode', [
                'online',
                'in_person',
                'home_visit',
                'phone_call',
            ])->default('online');
            $table->enum('status', [
                'pending',
                'ongoing',
                'confirmed',
                'cancelled',
                'completed',
                'rescheduled',
                'no_show',
                'rejected',
            ])->default('Pending');
            $table->date('appointment_date');
            $table->string('appointment_time');
            $table->string('duration_minutes')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            /* Follow-Up / Recurring */
            $table->boolean('is_follow_up')->default(false);
            $table->foreignUuid('parent_appointment_id')->nullable()->constrained('appointments');
            $table->enum('recurring_type', [
                'none',
                'weekly',
                'monthly',
                'custom',
            ])->default('none');
            $table->string('custom_recurring')->nullable();
            $table->dateTime('recurring_until')->nullable();

            /* Payment & Billing */
            $table->string('payment_status')->default('pending');
            $table->enum('payment_method', [
                'razorpay',
                'card',
                'cash',
            ])->nullable();
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();

            /* Technical Fields */
            $table->string('meeting_link')->nullable();
            $table->string('timezone')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('rescheduled_at')->nullable();
            $table->string('agora_channel_id')->nullable();
            $table->string('agora_channel_token')->nullable();

            /* Timestamps */
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
