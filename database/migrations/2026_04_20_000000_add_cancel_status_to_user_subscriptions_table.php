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
        // Update any 'cancelled' status to 'cancel' before modifying the enum
        DB::statement("UPDATE user_subscriptions SET status = 'cancelled' WHERE status = 'cancelled'");

        // Use raw SQL to modify the enum since Laravel doesn't support modifying enum values directly
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('pending', 'active', 'suspend', 'reject', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum back to original values (without 'cancel')
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('pending', 'active', 'suspend', 'reject') DEFAULT 'pending'");

        // Note: This migration doesn't update existing 'cancel' records back to 'cancelled'
        // as the original code was using 'cancelled' which wasn't in the enum anyway
    }
};
