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
        Schema::table('vaccine_temperatures', function (Blueprint $table) {
            // Make vaccine_id nullable (if it exists and is not nullable)
            if (Schema::hasColumn('vaccine_temperatures', 'vaccine_id')) {
                $table->foreignUuid('vaccine_id')->nullable()->change();
            } else {
                // Add vaccine_id if it doesn't exist
                $table->foreignUuid('vaccine_id')->nullable()->constrained()->nullOnDelete();
            }

            // Add date column (if not exists)
            if (! Schema::hasColumn('vaccine_temperatures', 'date')) {
                $table->date('date')->nullable()->after('vaccine_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccine_temperatures', function (Blueprint $table) {
            $table->dropForeign(['vaccine_id']);
            $table->dropColumn('vaccine_id');
            $table->dropColumn('date');
        });
    }
};
