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
        Schema::table('encounters', function (Blueprint $table) {
            $table->foreignUuid('patient_id')->constrained()->after('id');
            $table->foreignUuid('doctor_id')->constrained()->comment('also known as provider');
            $table->foreignUuid('hospital_id')->constrained();
            $table->foreignUuid('appointment_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            //
        });
    }
};
