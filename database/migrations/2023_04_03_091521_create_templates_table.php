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
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->string('default')->nullable();
            $table->string('name')->nullable();
            $table->string('age')->nullable();
            $table->string('category')->nullable();
            $table->string('sex')->nullable();
            $table->string('group')->nullable();
            $table->longText('data')->nullable();
            $table->longText('scoring')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
