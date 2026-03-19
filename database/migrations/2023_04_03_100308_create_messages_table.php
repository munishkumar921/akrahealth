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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('to')->constrained('users');
            $table->foreignUuid('from')->constrained('users');
            $table->foreignUuid('cc')->constrained('users');
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->foreignUuid('document_id')->constrained();
            $table->date('date')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
