<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make the 'cc' column nullable
        DB::statement('ALTER TABLE messages MODIFY COLUMN `cc` CHAR(36) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE messages MODIFY COLUMN `cc` CHAR(36) NOT NULL');
    }
};
