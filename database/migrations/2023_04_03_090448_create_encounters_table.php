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
        Schema::create('encounters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('chief_complaint')->nullable();
            $table->date('encounter_date')->nullable();
            $table->string('encounter_signed')->nullable();
            $table->date('date_signed')->nullable();
            $table->date('encounter_date_of_service')->nullable();
            $table->string('encounter_age')->nullable();
            $table->string('encounter_type')->nullable();
            $table->string('encounter_location')->nullable();
            $table->string('encounter_activity')->nullable();
            $table->string('complexity_of_encounter')->nullable();
            $table->string('encounter_cc')->nullable();
            $table->string('activity')->nullable();
            $table->longText('cc')->nullable();
            $table->string('encounter_condition')->nullable()->comment('Other Condition');
            $table->string('encounter_condition_work')->nullable()->comment('Condition Related To Work');
            $table->string('encounter_condition_auto')->nullable()->comment('Condition Related To Motor Vehicle Accident');
            $table->string('encounter_condition_auto_state')->nullable()->comment('State Where Motor Vehicle Accident Occurred');
            $table->string('encounter_condition_other')->nullable()->comment('Condition Related To Other Accident');
            $table->string('encounter_role')->nullable();
            $table->string('referring_provider')->nullable();
            $table->string('bill_submitted')->nullable();
            $table->string('addendum')->nullable();
            $table->bigInteger('addendum_eid')->nullable();
            $table->string('provider_role')->nullable();
            $table->string('referring_provider_npi')->nullable();
            $table->string('encounter_template')->nullable();
            $table->string('bill_complex')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
