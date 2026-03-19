<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiologies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->nullable()->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->cascadeOnDelete();
            // Basic Provider Info
            $table->string('name');
            $table->string('facility_type')->nullable(); // Diagnostic Center / Hospital / Imaging Center
            // Contact Info
            $table->string('website')->nullable();
            $table->boolean('is_verified')->default(false);

            // Other Details
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            // Status
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiologies');

    }
};
