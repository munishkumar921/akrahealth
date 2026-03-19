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
        Schema::table('insurances', function (Blueprint $table) {
            $table->uuid('address_id')->nullable()->constrained();
            $table->uuid('patient_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            // Drop foreign key if exists
            try {
                $table->dropForeign(['address_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            $table->dropColumn('address_id');
            $table->dropConstrainedForeignId('patient_id');
        });
    }
};
