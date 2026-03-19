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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('t_messages_id', 100)->nullable()->after('id');
            $table->string('mailbox', 50)->nullable()->after('t_messages_id');
            $table->string('status', 50)->default('active')->after('mailbox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['t_messages_id', 'mailbox', 'status']);
        });
    }
};
