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
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->enum('schedule_status', ['Start', 'End'])->nullable();
            $table->string('title')->nullable();
            $table->string('duration')->nullable();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->string('visit_type')->nullable();
            $table->text('reason')->nullable();
            $table->longText('notes')->nullable();
            $table->bigInteger('room_id')->nullable();
            $table->boolean('is_active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
