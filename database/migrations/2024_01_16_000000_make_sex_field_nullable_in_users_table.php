<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make sex field nullable and remove default value
        DB::statement("ALTER TABLE users MODIFY sex ENUM('Male', 'Female', 'Other', 'Non-binary', 'Prefer not to say') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to non-nullable with default
        DB::statement("ALTER TABLE users MODIFY sex ENUM('Male', 'Female', 'Other', 'Non-binary', 'Prefer not to say') NOT NULL DEFAULT 'Male'");
    }
};
