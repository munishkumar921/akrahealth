<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'cancelled' to the ENUM
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE user_subscriptions 
                MODIFY status ENUM(
                    'pending',
                    'active',
                    'suspend',
                    'reject',
                    'replaced',
                    'expired',
                    'cancelled'
                ) DEFAULT 'pending'
            ");
        }
    }

    public function down(): void
    {
        // Remove 'cancelled' from the ENUM
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE user_subscriptions 
                MODIFY status ENUM(
                    'pending',
                    'active',
                    'suspend',
                    'reject',
                    'replaced',
                    'expired'
                ) DEFAULT 'pending'
            ");
        }
    }
};
