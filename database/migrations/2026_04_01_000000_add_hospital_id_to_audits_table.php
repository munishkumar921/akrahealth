<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('audits', 'hospital_id')) {
            Schema::table('audits', function (Blueprint $table) {
                $table->foreignUuid('hospital_id')
                    ->nullable()
                    ->after('admin_id')
                    ->constrained('hospitals')
                    ->nullOnDelete();
            });
        }

        // Backfill from rows where admin_id is a user id tied to a hospital.
        DB::statement("
            UPDATE audits a
            LEFT JOIN users admin_users ON admin_users.id = a.admin_id
            LEFT JOIN hospitals admin_hospitals ON admin_hospitals.user_id = admin_users.id
            SET a.hospital_id = COALESCE(admin_users.hospital_id, admin_hospitals.id)
            WHERE a.hospital_id IS NULL
        ");

        // Backfill older rows where admin_id was incorrectly stored as a hospital id.
        DB::statement("
            UPDATE audits a
            INNER JOIN hospitals h ON h.id = a.admin_id
            SET a.hospital_id = h.id
            WHERE a.hospital_id IS NULL
        ");

        // Fallback through user_id when admin_id is missing.
        DB::statement("
            UPDATE audits a
            LEFT JOIN users actor_users ON actor_users.id = a.user_id
            LEFT JOIN hospitals actor_hospitals ON actor_hospitals.user_id = actor_users.id
            SET a.hospital_id = COALESCE(actor_users.hospital_id, actor_hospitals.id)
            WHERE a.hospital_id IS NULL
        ");

        Schema::table('audits', function (Blueprint $table) {
            if (! DB::select("SHOW INDEX FROM audits WHERE Key_name = 'audits_hospital_id_created_at_index'")) {
                $table->index(['hospital_id', 'created_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('audits', 'hospital_id')) {
            return;
        }

        Schema::table('audits', function (Blueprint $table) {
            try {
                $table->dropIndex(['hospital_id', 'created_at']);
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
