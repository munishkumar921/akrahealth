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
        Schema::table('patient_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_notes', 'imm_note')) {
                $table->text('imm_note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_notes', function (Blueprint $table) {
            if (Schema::hasColumn('patient_notes', 'imm_note')) {
                $table->dropColumn('imm_note');
            }
        });
    }
};
