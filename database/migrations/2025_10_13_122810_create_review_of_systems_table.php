<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('review_of_systems', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->nullable()->constrained();
            $table->string('encounter_provider', 255)->nullable();
            $table->longText('ros')->nullable();
            $table->longText('ros_gen')->nullable();
            $table->longText('ros_eye')->nullable();
            $table->longText('ros_ent')->nullable();
            $table->longText('ros_resp')->nullable();
            $table->longText('ros_cv')->nullable();
            $table->longText('ros_gi')->nullable();
            $table->longText('ros_gu')->nullable();
            $table->longText('ros_mus')->nullable();
            $table->longText('ros_neuro')->nullable();
            $table->longText('ros_psych')->nullable();
            $table->longText('ros_heme')->nullable();
            $table->longText('ros_endocrine')->nullable();
            $table->longText('ros_skin')->nullable();
            $table->longText('ros_wcc')->nullable();
            $table->longText('ros_psych1')->nullable();
            $table->longText('ros_psych2')->nullable();
            $table->longText('ros_psych3')->nullable();
            $table->longText('ros_psych4')->nullable();
            $table->longText('ros_psych5')->nullable();
            $table->longText('ros_psych6')->nullable();
            $table->longText('ros_psych7')->nullable();
            $table->longText('ros_psych8')->nullable();
            $table->longText('ros_psych9')->nullable();
            $table->longText('ros_psych10')->nullable();
            $table->longText('ros_psych11')->nullable();
            $table->timestamp('ros_date')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_of_systems');
    }
};
