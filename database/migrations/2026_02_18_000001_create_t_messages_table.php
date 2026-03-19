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
        Schema::create('t_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('patient_id')->nullable()->nullable()->constrained();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->foreignUuid('hospital_id')->nullable()->constrained()->onDelete('set null');
            $table->string('to')->nullable();
            $table->string('from')->nullable();
            $table->text('messages_signed')->nullable();
            $table->date('date')->nullable();
            $table->date('messages_dos')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_messages');
    }
};
