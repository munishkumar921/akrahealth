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
            $table->foreignUuid('doctor_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('read_at')->nullable()->after('read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
            $table->dropColumn('read_at');
        });
    }
};
