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
        Schema::create('allergies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->date('date_active')->nullable();
            $table->date('date_inactive')->nullable();
            $table->text('allergies_medicine')->nullable();
            $table->text('allergies_reaction')->nullable();
            $table->text('allergies_severity')->nullable();
            $table->text('rcopia_sync')->nullable();
            $table->text('medicine_ndcid')->nullable();
            $table->text('reconcile')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allergies');
    }
};
