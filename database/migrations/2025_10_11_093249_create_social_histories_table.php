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
        Schema::create('social_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->text('social_history')->nullable();
            $table->boolean('sexually_active')->nullable();
            $table->text('diet')->nullable();
            $table->text('physical_activity')->nullable();
            $table->text('employment')->nullable();
            $table->text('tobacco_use')->nullable();
            $table->text('alcohol_use')->nullable();
            $table->text('drug_use')->nullable();
            $table->text('mental_health_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_histories');
    }
};
