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
        Schema::table('social_histories', function (Blueprint $table) {
            $table->text('tobacco_use_details')->nullable()->after('tobacco_use');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_histories', function (Blueprint $table) {
            $table->dropColumn('tobacco_use_details');
        });
    }
};
