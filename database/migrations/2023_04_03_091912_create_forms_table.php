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
        Schema::create('forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained();
            $table->date('date')->nullable();
            $table->string('title');
            $table->longText('content');
            $table->string('destination')->nullable();
            $table->longText('content_text')->nullable();
            $table->longText('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
