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
        Schema::create('vaccine_temperatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vaccine_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->timestamp('time')->nullable();
            $table->string('temperature')->nullable();
            $table->longText('action')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccine_temperatures');
    }
};
