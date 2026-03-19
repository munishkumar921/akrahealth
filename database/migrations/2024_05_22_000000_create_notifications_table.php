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
        // Custom notifications table for UUID notifiable_id
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Foreign keys
            $table->foreignUuid('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('notifiable_type')->nullable();
            $table->uuid('notifiable_id'); // Change to uuid if using UUIDs
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
