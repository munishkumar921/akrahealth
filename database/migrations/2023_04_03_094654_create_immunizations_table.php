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
        Schema::create('immunizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('current_procedural_terminology_id')->constrained();
            $table->date('date')->nullable();
            $table->string('immunization')->nullable();
            $table->string('sequence')->nullable();
            $table->string('body_site')->nullable();
            $table->string('dosage')->nullable();
            $table->string('dosage_unit')->nullable();
            $table->string('route')->nullable();
            $table->string('elsewhere')->nullable();
            $table->string('vis')->nullable()->comment('Vaccine Information Statements');
            $table->string('lot')->nullable()->comment('left occiput transverse');
            $table->string('manufacturer')->nullable();
            $table->date('expiration')->nullable();
            $table->string('brand')->nullable();
            $table->string('cvx_code')->nullable()->comment('Source Synopsis. HL7 Table 0292, Vaccine Administered');
            $table->string('reconcile')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immunizations');
    }
};
