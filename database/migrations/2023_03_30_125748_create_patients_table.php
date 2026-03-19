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
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('blood_group')->nullable();
            $table->string('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->text('existing_conditions')->nullable()->comment('e.g. "Diabetes, Hypertension"');
            $table->text('current_medications')->nullable();
            $table->text('past_surgeries')->nullable()->comment('e.g. "Appendectomy, Gallbladder removal"');
            /* insurance detail */
            $table->string('insurance_provider')->nullable()->comment('e.g. "ABC Health Insurance"');
            $table->string('policy_number')->nullable()->comment('e.g. "1234567890"');
            $table->date('policy_start_date')->nullable();
            $table->date('policy_end_date')->nullable();
            $table->string('insurance_document')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
