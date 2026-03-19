<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('last_payment_reminder_at')->nullable()->after('payment_status');
            $table->integer('payment_reminder_count')->default(0)->after('last_payment_reminder_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['last_payment_reminder_at', 'payment_reminder_count']);
        });
    }
};
