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
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->nullable()->constrained();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->string('encounter_provider')->nullable();
            $table->timestamp('assessment_date')->nullable();
            $table->longText('assessment_other')->nullable();
            $table->longText('assessment_ddx')->nullable();
            $table->longText('assessment_notes')->nullable();
            $table->longText('assessment_1')->nullable();
            $table->longText('assessment_2')->nullable();
            $table->longText('assessment_3')->nullable();
            $table->longText('assessment_4')->nullable();
            $table->longText('assessment_5')->nullable();
            $table->longText('assessment_6')->nullable();
            $table->longText('assessment_7')->nullable();
            $table->longText('assessment_8')->nullable();
            $table->longText('assessment_9')->nullable();
            $table->longText('assessment_10')->nullable();
            $table->longText('assessment_11')->nullable();
            $table->longText('assessment_12')->nullable();

            $table->text('assessment_icd1')->nullable();
            $table->text('assessment_icd2')->nullable();
            $table->text('assessment_icd3')->nullable();
            $table->text('assessment_icd4')->nullable();
            $table->text('assessment_icd5')->nullable();
            $table->text('assessment_icd6')->nullable();
            $table->text('assessment_icd7')->nullable();
            $table->text('assessment_icd8')->nullable();
            $table->text('assessment_icd9')->nullable();
            $table->text('assessment_icd10')->nullable();
            $table->text('assessment_icd11')->nullable();
            $table->text('assessment_icd12')->nullable();

            $table->longText('other')->nullable();
            $table->longText('differential_diagnoses')->nullable();
            $table->longText('assessment_discussion')->nullable()->comment('Discussion with patient/family or notes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
