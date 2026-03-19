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
        Schema::create('patient_relate', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('hospital_id');
            $table->string('appointment_reminder')->nullable();
            $table->string('api_key')->nullable();
            $table->string('url')->nullable();
            $table->string('uma_client_id')->nullable();
            $table->string('uma_client_secret')->nullable();
            $table->text('uma_refresh_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_relate');
    }
};
