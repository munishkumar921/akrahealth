<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immunizations', function (Blueprint $table) {
            // First, drop the foreign key constraint if it exists
            $table->dropForeign(['current_procedural_terminology_id']);

            // Then, modify the column to be nullable
            $table->foreignUuid('current_procedural_terminology_id')
                ->nullable()
                ->change();

            // Re-add the foreign key constraint
            $table->foreign('current_procedural_terminology_id')
                ->references('id')
                ->on('current_procedural_terminologies')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('immunizations', function (Blueprint $table) {
            $table->dropForeign(['current_procedural_terminology_id']);

            $table->foreignUuid('current_procedural_terminology_id')
                ->nullable(false)
                ->change();

            $table->foreign('current_procedural_terminology_id')
                ->references('id')
                ->on('current_procedural_terminologies')
                ->onDelete('cascade');
        });
    }
};
