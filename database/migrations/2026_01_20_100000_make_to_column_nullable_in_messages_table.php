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
        // Simply modify the column to be nullable - MySQL allows this without dropping FK
        DB::statement('ALTER TABLE messages MODIFY COLUMN `to` CHAR(36) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE messages MODIFY COLUMN `to` CHAR(36) NOT NULL');
    }
};
