<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration safely adds columns to the audits table only if they don't exist.
     * The original 2023_04_03_065413_create_audits_table.php migration already
     * includes these columns, so this migration will typically do nothing.
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {

            $hasUserId = Schema::hasColumn('audits', 'user_id');

            // admin_id
            if (! Schema::hasColumn('audits', 'admin_id')) {
                $hasUserId
                    ? $table->string('admin_id', 36)->nullable()->after('user_id')
                    : $table->string('admin_id', 36)->nullable();
            }

            // user_type
            if (! Schema::hasColumn('audits', 'user_type')) {
                $table->string('user_type')->nullable()->after('admin_id');
            }

            // module
            if (! Schema::hasColumn('audits', 'module')) {
                $table->string('module')->nullable()->after('user_type');
            }

            // description
            if (! Schema::hasColumn('audits', 'description')) {
                $table->text('description')->nullable()->after('action');
            }

            // ip_address
            if (! Schema::hasColumn('audits', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('description');
            }

            // user_agent
            if (! Schema::hasColumn('audits', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            // old_values
            if (! Schema::hasColumn('audits', 'old_values')) {
                $table->json('old_values')->nullable()->after('user_agent');
            }

            // new_values
            if (! Schema::hasColumn('audits', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }

            // encounter_location
            if (! Schema::hasColumn('audits', 'encounter_location')) {
                $table->text('encounter_location')->nullable()->after('new_values');
            }
        });

        /**
         * Indexes — only create if columns exist
         */
        Schema::table('audits', function (Blueprint $table) {

            if (
                Schema::hasColumn('audits', 'user_id') &&
                ! DB::select("SHOW INDEX FROM audits WHERE Key_name = 'audits_user_id_created_at_index'")
            ) {
                $table->index(['user_id', 'created_at']);
            }

            if (
                Schema::hasColumn('audits', 'admin_id') &&
                ! DB::select("SHOW INDEX FROM audits WHERE Key_name = 'audits_admin_id_created_at_index'")
            ) {
                $table->index(['admin_id', 'created_at']);
            }

            if (
                Schema::hasColumn('audits', 'module') &&
                Schema::hasColumn('audits', 'action') &&
                ! DB::select("SHOW INDEX FROM audits WHERE Key_name = 'audits_module_action_index'")
            ) {
                $table->index(['module', 'action']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {

            if (Schema::hasColumn('audits', 'encounter_location')) {
                $table->dropColumn('encounter_location');
            }
            if (Schema::hasColumn('audits', 'new_values')) {
                $table->dropColumn('new_values');
            }
            if (Schema::hasColumn('audits', 'old_values')) {
                $table->dropColumn('old_values');
            }
            if (Schema::hasColumn('audits', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            if (Schema::hasColumn('audits', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('audits', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('audits', 'module')) {
                $table->dropColumn('module');
            }
            if (Schema::hasColumn('audits', 'user_type')) {
                $table->dropColumn('user_type');
            }
            if (Schema::hasColumn('audits', 'admin_id')) {
                $table->dropColumn('admin_id');
            }

            // Drop indexes safely
            try {
                $table->dropIndex(['user_id', 'created_at']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['admin_id', 'created_at']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['module', 'action']);
            } catch (\Throwable $e) {
            }
        });
    }
};
