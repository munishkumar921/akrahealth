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
        Schema::table('supplements', function (Blueprint $table) {
            $table->dropForeign(['current_procedural_terminology_id']);
            $table->dropColumn('current_procedural_terminology_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplements', function (Blueprint $table) {
            //
        });
    }
};
