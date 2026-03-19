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
        Schema::create('national_provider_identifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code');
            $table->longText('type')->nullable();
            $table->longText('classification')->nullable();
            $table->longText('specialization')->nullable();
            $table->longText('taxonomy')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('national_provider_identifiers');
    }
};
