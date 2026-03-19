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
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('lab_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('lab_test_id')->constrained();

            $table->enum('status', [
                'pending',
                'accepted',
                'in_progress',
                'completed',
                'cancelled',
                'rejected',
                'no_show',
            ])->default('pending');

            $table->dateTime('scheduled_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->text('notes')->nullable();

            $table->string('report_file')->nullable();

            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->boolean('pickup_required')->nullable();
            $table->boolean('pickup_completed')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
