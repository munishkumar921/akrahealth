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
        Schema::table('forms', function (Blueprint $table) {
            // Add new columns
            $table->foreignUuid('doctor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            // Drop the columns if rolled back
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropConstrainedForeignId('patient_id');
        });
    }
};
