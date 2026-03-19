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
        Schema::create('vitals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->timestamp('vital_date')->nullable();
            $table->string('age')->nullable();
            $table->string('passage')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('head_circumference')->nullable();
            $table->string('bmi')->nullable()->comment('Body Mass Index');
            $table->string('temperature')->nullable();
            $table->string('temperature_method')->nullable();
            $table->string('bp_systolic')->nullable();
            $table->string('bp_diastolic')->nullable();
            $table->string('bp_position')->nullable();
            $table->string('pulse')->nullable();
            $table->string('respirations')->nullable();
            $table->string('o2_saturation')->nullable();
            $table->longText('vitals_other')->nullable();
            $table->string('wt_percentile')->nullable();
            $table->string('ht_percentile')->nullable();
            $table->string('hc_percentile')->nullable();
            $table->string('wt_ht_percentile')->nullable();
            $table->string('bmi_percentile')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};
