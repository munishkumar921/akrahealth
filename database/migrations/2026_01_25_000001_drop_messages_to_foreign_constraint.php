<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration drops the foreign key constraints from the 'to' and 'cc' columns in the messages table.
     * These columns were made nullable in previous migrations, but the FK constraints were not removed,
     * causing constraint violations when storing non-existent user IDs or empty strings.
     */
    public function up(): void
    {
        // Drop foreign key constraint for 'to' column if it exists
        $toConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'messages' 
            AND REFERENCED_TABLE_NAME = 'users'
            AND COLUMN_NAME = 'to'
        ");

        if (! empty($toConstraints)) {
            $constraintName = $toConstraints[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE messages DROP FOREIGN KEY {$constraintName}");
        }

        // Drop foreign key constraint for 'cc' column if it exists
        $ccConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'messages' 
            AND REFERENCED_TABLE_NAME = 'users'
            AND COLUMN_NAME = 'cc'
        ");

        if (! empty($ccConstraints)) {
            $constraintName = $ccConstraints[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE messages DROP FOREIGN KEY {$constraintName}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the foreign key constraints
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_to_foreign FOREIGN KEY (`to`) REFERENCES `users`(`id`) ON DELETE CASCADE');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_cc_foreign FOREIGN KEY (`cc`) REFERENCES `users`(`id`) ON DELETE CASCADE');
    }
};
