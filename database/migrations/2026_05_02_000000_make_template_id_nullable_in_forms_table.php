<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {

            // 🔹 Drop existing foreign key first
            $table->dropForeign(['template_id']);

            // 🔹 Make column nullable
            $table->uuid('template_id')->nullable()->change();

            // 🔹 Re-add foreign key
            $table->foreign('template_id')
                ->references('id')
                ->on('templates')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {

            // 🔹 Drop FK
            $table->dropForeign(['template_id']);

            // 🔹 Make column NOT nullable
            $table->uuid('template_id')->nullable(false)->change();

            // 🔹 Re-add FK
            $table->foreign('template_id')
                ->references('id')
                ->on('templates')
                ->cascadeOnDelete();
        });
    }
};
