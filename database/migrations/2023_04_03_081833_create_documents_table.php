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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('hospital_id')->constrained()->nullable()->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('url')->nullable();
            $table->string('description')->nullable();
            $table->string('from')->nullable();
            $table->string('viewed')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
