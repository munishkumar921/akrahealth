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
        Schema::create('hospital_timings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained()->onDelete('cascade');
            $table->boolean('monday')->default(false);
            $table->boolean('tuesday')->default(false);
            $table->boolean('wednesday')->default(false);
            $table->boolean('thursday')->default(false);
            $table->boolean('friday')->default(false);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->string('morning_slot')->nullable();
            $table->string('afternoon_slot')->nullable();
            $table->string('evening_slot')->nullable();
            $table->string('night_slot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_timings');
    }
};
