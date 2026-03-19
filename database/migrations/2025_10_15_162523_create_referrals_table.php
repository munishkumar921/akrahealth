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
        Schema::create('referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->nullable()->constrained();
            $table->foreignUuid('insurance_id')->nullable()->constrained('insurances');
            $table->foreignUuid('doctor_id')->nullable()->constrained();
            $table->text('detail')->nullable();
            $table->string('code')->nullable();
            $table->string('specialty')->nullable();
            $table->date('pending_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
