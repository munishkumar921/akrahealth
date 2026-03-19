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
        Schema::create('doctor_patient', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->boolean('accept_term_condition')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_patient');
    }
};
