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
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->nullable()->constrained();
            $table->foreignUuid('encounter_id')->constrained();
            $table->date('date')->nullable();
            $table->longText('plan')->nullable();
            $table->string('duration')->nullable();
            $table->longText('followup')->nullable();
            $table->longText('goals')->nullable();
            $table->longText('tp')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
