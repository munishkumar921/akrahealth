<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->text('services')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
