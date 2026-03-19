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
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Use foreignUuid() instead of foreignId() since users.id is UUID (CHAR(36))
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignUuid('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_type')->nullable();
            $table->string('module')->nullable();
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('query')->nullable();
            $table->string('encounter_location')->nullable();
            $table->timestamps();

            // Add index for faster queries
            $table->index(['user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
            $table->index(['module', 'action']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
