<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include more gender options
        DB::statement("ALTER TABLE users MODIFY sex ENUM('Male', 'Female', 'Other', 'Non-binary', 'Prefer not to say') DEFAULT 'Male'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE users MODIFY sex ENUM('Male', 'Female', 'Other') DEFAULT 'Male'");
    }
};
