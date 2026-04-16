<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bank_accounts') || Schema::hasColumn('bank_accounts', 'hospital_id')) {
            return;
        }

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->char('hospital_id', 36)->nullable()->after('user_id');
            $table->foreign('hospital_id')->references('id')->on('hospitals')->nullOnDelete();
            $table->index(['hospital_id', 'is_primary']);
            $table->index(['hospital_id', 'is_active']);
        });

        DB::statement('
            UPDATE bank_accounts ba
            LEFT JOIN users u ON u.id = ba.user_id
            LEFT JOIN hospitals h ON h.user_id = ba.user_id
            SET ba.hospital_id = COALESCE(u.hospital_id, h.id)
            WHERE ba.hospital_id IS NULL
        ');
    }

    public function down(): void
    {
        if (! Schema::hasTable('bank_accounts') || ! Schema::hasColumn('bank_accounts', 'hospital_id')) {
            return;
        }

        Schema::table('bank_accounts', function (Blueprint $table) {
            try {
                $table->dropIndex(['hospital_id', 'is_primary']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex(['hospital_id', 'is_active']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropForeign(['hospital_id']);
            } catch (\Throwable $e) {
            }

            $table->dropColumn('hospital_id');
        });
    }
};
