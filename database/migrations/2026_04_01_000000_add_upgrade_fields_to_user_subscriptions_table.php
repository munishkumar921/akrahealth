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
        // Add new status values to the enum
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('pending', 'active', 'suspend', 'reject', 'expired', 'replaced', 'pending_upgrade') DEFAULT 'pending'");

        // Add new columns for upgrade tracking
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->timestamp('replaced_at')->nullable()->after('end_date');
            $table->uuid('replaced_by')->nullable()->after('replaced_at');
            $table->uuid('upgraded_from')->nullable()->after('replaced_by');
        });

        // Add foreign key for replaced_by
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->foreign('replaced_by')->references('id')->on('user_subscriptions')->onDelete('set null');
            $table->foreign('upgraded_from')->references('id')->on('user_subscriptions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['replaced_by']);
            $table->dropForeign(['upgraded_from']);
        });

        // Remove new columns
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['replaced_at', 'replaced_by', 'upgraded_from']);
        });

        // Revert enum to original values
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('pending', 'active', 'suspend', 'reject') DEFAULT 'pending'");
    }
};
