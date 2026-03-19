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
        Schema::create('tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamp('time')->nullable();
            $table->longText('result')->nullable();
            $table->string('units')->nullable();
            $table->longText('reference')->nullable();
            $table->string('flags')->nullable();
            $table->longText('unassigned')->nullable();
            $table->longText('_from')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
