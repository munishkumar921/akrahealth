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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->nullable()->constrained();
            $table->foreignUuid('patient_id')->nullable()->constrained();
            $table->foreignUuid('doctor_id')->nullable()->constrained();
            $table->foreignUuid('address_id')->nullable()->constrained();
            $table->foreignUuid('encounter_provider')->nullable();
            $table->foreignUuid('insurance_id')->nullable()->constrained('insurances');
            $table->timestamp('orders_date')->nullable();
            $table->longText('referrals')->nullable();
            $table->longText('labs')->nullable();
            $table->longText('radiology')->nullable();
            $table->longText('cp')->nullable();
            $table->longText('referrals_icd')->nullable();
            $table->longText('labs_icd')->nullable();
            $table->longText('radiology_icd')->nullable();
            $table->longText('cp_icd')->nullable();
            $table->string('labs_obtained')->nullable();
            $table->longText('notes', 255)->nullable();
            $table->dateTime('pending_date')->nullable();
            $table->boolean('is_completed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
