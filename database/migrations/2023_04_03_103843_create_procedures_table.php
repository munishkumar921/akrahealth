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
        Schema::create('procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->string('encounter_provider')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->string('cpt')->nullable()->comment('Current Procedural Terminology');
            $table->longText('description')->nullable();
            $table->longText('complications')->nullable();
            $table->longText('ebl')->nullable()->comment('estimated blood loss');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
