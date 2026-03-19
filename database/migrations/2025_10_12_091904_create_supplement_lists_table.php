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
        Schema::create('supplement_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->nullable();
            $table->foreignUuid('supplement_id')->nullable();
            $table->string('reconcile', 255)->nullable();
            $table->dateTime('sup_date_active')->nullable();
            $table->dateTime('sup_date_prescribed')->nullable();
            $table->string('sup_supplement', 255)->nullable();
            $table->string('sup_dosage', 255)->nullable();
            $table->string('sup_dosage_unit', 255)->nullable();
            $table->string('sup_sig', 255)->nullable();
            $table->string('sup_route', 255)->nullable();
            $table->string('sup_frequency', 255)->nullable();
            $table->string('sup_instructions', 255)->nullable();
            $table->string('sup_quantity', 255)->nullable();
            $table->string('sup_reason', 255)->nullable();
            $table->dateTime('sup_date_inactive')->nullable();
            $table->dateTime('sup_date_old')->nullable();
            $table->string('sup_provider', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplement_lists');
    }
};
