<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration safely adds columns to the audits table using raw SQL
     * with IF NOT EXISTS checks. The original 2023_04_03_065413_create_audits_table.php
     * migration already includes these columns, so this migration will typically do nothing.
     */
    public function up(): void
    {
        // Get existing columns to check which ones need to be added
        $existingColumns = DB::select("SHOW COLUMNS FROM audits LIKE 'audits'");
        $columns = DB::select('DESCRIBE audits');
        $existingColumnNames = array_map(function ($col) {
            return $col->Field;
        }, $columns);

        // Add admin_id column if it doesn't exist
        if (! in_array('admin_id', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN admin_id CHAR(36) NULL AFTER user_id');
        }

        // Add user_type column if it doesn't exist
        if (! in_array('user_type', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN user_type VARCHAR(255) NULL AFTER admin_id');
        }

        // Add module column if it doesn't exist
        if (! in_array('module', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN module VARCHAR(255) NULL AFTER user_type');
        }

        // Add description column if it doesn't exist
        if (! in_array('description', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN description TEXT NULL AFTER module');
        }

        // Add ip_address column if it doesn't exist
        if (! in_array('ip_address', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN ip_address VARCHAR(45) NULL AFTER description');
        }

        // Add user_agent column if it doesn't exist
        if (! in_array('user_agent', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN user_agent TEXT NULL AFTER ip_address');
        }

        // Add old_values column if it doesn't exist
        if (! in_array('old_values', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN old_values JSON NULL AFTER user_agent');
        }

        // Add new_values column if it doesn't exist
        if (! in_array('new_values', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN new_values JSON NULL AFTER old_values');
        }

        // Add encounter_location column if it doesn't exist
        if (! in_array('encounter_location', $existingColumnNames)) {
            DB::statement('ALTER TABLE audits ADD COLUMN encounter_location TEXT NULL AFTER new_values');
        }

        // Add indexes only if they don't exist
        $indexes = DB::select('SHOW INDEX FROM audits');
        $indexNames = array_unique(array_map(function ($idx) {
            return $idx->Key_name;
        }, $indexes));

        if (! in_array('idx_audits_user_created', $indexNames)) {
            DB::statement('ALTER TABLE audits ADD INDEX idx_audits_user_created (user_id, created_at)');
        }

        if (! in_array('idx_audits_admin_created', $indexNames)) {
            DB::statement('ALTER TABLE audits ADD INDEX idx_audits_admin_created (admin_id, created_at)');
        }

        if (! in_array('idx_audits_module_action', $indexNames)) {
            DB::statement('ALTER TABLE audits ADD INDEX idx_audits_module_action (module, action)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        DB::statement('ALTER TABLE audits DROP INDEX IF EXISTS idx_audits_user_created');
        DB::statement('ALTER TABLE audits DROP INDEX IF EXISTS idx_audits_admin_created');
        DB::statement('ALTER TABLE audits DROP INDEX IF EXISTS idx_audits_module_action');

        // Drop columns
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS admin_id');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS user_type');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS module');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS description');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS ip_address');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS user_agent');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS old_values');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS new_values');
        DB::statement('ALTER TABLE audits DROP COLUMN IF EXISTS encounter_location');
    }
};
