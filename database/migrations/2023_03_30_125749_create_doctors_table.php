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
        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /* Foreign Keys */
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('hospital_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('selected_patient_id')->nullable()->constrained('patients')->onDelete('set null');

            /* Professional Info */
            $table->string('registration_number')->nullable();
            $table->string('certification')->nullable()->comment('e.g. "Certificate image URL or path "');
            $table->string('government_id_proof')->nullable()->comment('e.g. "PAN card, Aadhaar card, etc."');
            $table->string('doctor_logo')->nullable();
            $table->string('doctor_seal')->nullable();
            $table->string('doctor_signature')->nullable();
            $table->string('qualification')->nullable()->comment('e.g. MBBS, MD');
            $table->string('experience')->nullable()->comment('e.g. "10 years"');
            $table->text('about')->nullable();
            $table->decimal('rating', 8, 2)->nullable()->comment('e.g. "4.5"');

            /* Availability & Preferences */
            $table->string('consultation_fee')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('dea')->nullable()->comment('Drug Enforcement Administration number, if applicable');
            $table->string('tax_id')->nullable()->comment('PAN/GST or TIN');
            $table->string('signature')->nullable();
            $table->integer('appointment_slot_duration')->default(15)->comment('it will store time duration of the doctor appointment like 15 or 30 etc. and this number will be in the minutes');

            /* Consultation Modes */
            $table->boolean('in_person_consultation')->default(false);
            $table->boolean('video_consultation')->default(false);
            $table->boolean('whatsapp_consultation')->default(false);

            /* Flags */
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
