<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration increases the size of the 'to' column in the messages table
     * to accommodate multiple user IDs separated by semicolons.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE messages MODIFY COLUMN `to` VARCHAR(500) NULL');
        DB::statement('ALTER TABLE messages MODIFY COLUMN `cc` VARCHAR(500) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE messages MODIFY COLUMN `to` CHAR(36) NULL');
        DB::statement('ALTER TABLE messages MODIFY COLUMN `cc` CHAR(36) NULL');
    }
};
