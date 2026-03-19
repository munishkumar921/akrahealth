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
        Schema::create('cardiopulmonary', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->foreignUuid('hospital_id')->nullable()->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->cascadeOnDelete();
            $table->string('facility_type')->default('Cardiopulmonary');
            $table->string('created_by');
            $table->string('website')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardiopulmonary');
    }
};
