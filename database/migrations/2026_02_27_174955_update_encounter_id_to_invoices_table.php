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
        if (! Schema::hasTable('invoices') || Schema::hasColumn('invoices', 'encounter_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('encounter_id')->after('appointment_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasColumn('invoices', 'encounter_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            try {
                $table->dropForeign(['encounter_id']);
            } catch (\Throwable $e) {
                // Ignore missing foreign key constraints in divergent schemas.
            }

            $table->dropColumn('encounter_id');
        });
    }
};
