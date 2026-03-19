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
        Schema::create('billing_cores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->nullable()->constrained();
            $table->foreignUuid('hospital_id')->nullable()->constrained();
            $table->integer('other_billing_id')->nullable();
            $table->string('cpt')->nullable();
            $table->string('cpt_charge')->nullable();
            $table->string('icd_pointer')->nullable();
            $table->string('unit')->nullable();
            $table->string('modifier')->nullable();
            $table->string('dos_f')->nullable();
            $table->string('dos_t')->nullable();
            $table->string('billing_group')->nullable();
            $table->string('payment')->nullable();
            $table->string('reason')->nullable();
            $table->string('payment_type')->nullable();
            $table->date('service_start')->nullable();
            $table->date('service_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_cores');
    }
};
