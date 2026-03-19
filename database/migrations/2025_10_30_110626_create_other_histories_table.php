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
        Schema::create('other_histories', function (Blueprint $table) {
            // Use UUID as primary key
            $table->uuid('id')->primary();

            // Foreign keys
            $table->foreignUuid('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('encounter_id')->nullable()->constrained('encounters')->cascadeOnDelete();

            // Encounter provider information
            $table->string('encounter_provider', 255)->nullable();

            // Date field - consider using date or datetime if you don't need timestamp
            $table->timestamp('oh_date')->useCurrent();

            // Medical history fields
            $table->longText('oh_pmh')->nullable(); // Past Medical History
            $table->longText('oh_psh')->nullable(); // Past Surgical History
            $table->longText('oh_fh')->nullable();  // Family History
            $table->longText('oh_sh')->nullable();  // Social History
            $table->longText('oh_etoh')->nullable(); // Alcohol History
            $table->longText('oh_tobacco')->nullable(); // Tobacco History
            $table->longText('oh_drugs')->nullable(); // Drug History
            $table->longText('oh_employment')->nullable(); // Employment History
            $table->longText('oh_meds')->nullable(); // Medications
            $table->longText('oh_supplements')->nullable(); // Supplements
            $table->longText('oh_allergies')->nullable(); // Allergies

            // Timestamps
            $table->timestamps();

            // Indexes for better performance
            $table->index('patient_id');
            $table->index('encounter_id');
            $table->index('oh_date');
            $table->index(['patient_id', 'oh_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_histories');
    }
};
