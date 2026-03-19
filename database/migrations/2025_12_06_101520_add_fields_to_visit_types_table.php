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
        Schema::table('visit_types', function (Blueprint $table) {
            $table->uuid('hospital_id');
            $table->uuid('doctor_id');
            $table->String('currency')->nullable();
            $table->String('price')->nullable();
            $table->String('duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_types', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropColumn(['hospital_id', 'doctor_id', 'currency', 'price', 'duration']);
        });
    }
};
