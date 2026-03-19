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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            /* Foreign keys */
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('pharmacy_id')->nullable()->constrained();

            /* Prescription info */
            $table->longText('rx')->nullable();
            $table->longText('supplements')->nullable();
            $table->longText('immunizations')->nullable();
            $table->longText('orders_summary')->nullable();
            $table->longText('supplements_orders_summary')->nullable();
            $table->string('prescription')->nullable();
            $table->string('medication')->nullable();
            $table->string('dosage')->nullable();
            $table->string('dosage_unit')->nullable();
            $table->string('sig')->nullable();
            $table->string('route')->nullable();
            $table->string('frequency')->nullable();
            $table->string('instructions')->nullable();
            $table->string('quantity')->nullable();
            $table->string('refill')->nullable();
            $table->string('reason')->nullable();
            $table->date('date_active')->nullable();
            $table->date('date_inactive')->nullable();
            $table->string('date_old')->nullable();
            $table->string('provider')->nullable();
            $table->string('dea')->nullable();
            $table->string('daw')->nullable();
            $table->string('license')->nullable();
            $table->integer('days')->nullable();
            $table->date('due_date')->nullable();
            $table->string('rcopia_sync')->nullable();
            $table->string('national_drug_code')->nullable();
            $table->string('reconcile')->nullable();
            $table->json('json')->nullable();
            $table->string('transaction')->nullable();

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
        Schema::dropIfExists('prescriptions');
    }
};
