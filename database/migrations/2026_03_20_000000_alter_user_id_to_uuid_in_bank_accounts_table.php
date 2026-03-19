<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the composite indexes first (if they exist)
        try {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'is_primary']);
            });
        } catch (\Exception $e) {
            // Index may not exist, ignore
        }

        try {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'is_active']);
            });
        } catch (\Exception $e) {
            // Index may not exist, ignore
        }

        // Drop the simple index if it exists
        try {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
            });
        } catch (\Exception $e) {
            // Index may not exist, ignore
        }

        // Drop foreign key if it exists (wrapped in try-catch)
        try {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Exception $e) {
            // Foreign key may not exist, ignore
        }

        // Modify the user_id column type from unsignedBigInteger to CHAR(36) (UUID)
        DB::statement('ALTER TABLE bank_accounts MODIFY user_id CHAR(36) NULL');

        // Re-add the composite indexes
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the composite indexes first
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_primary']);
            $table->dropIndex(['user_id', 'is_active']);
        });

        // Change back to unsignedBigInteger
        DB::statement('ALTER TABLE bank_accounts MODIFY user_id BIGINT UNSIGNED NULL');

        // Re-add the composite indexes
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'is_active']);
        });
    }
};
