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
        if (!Schema::hasTable('insurances') || Schema::hasColumn('insurances', 'comment')) {
            return;
        }

        Schema::table('insurances', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('insurance_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('insurances') || !Schema::hasColumn('insurances', 'comment')) {
            return;
        }

        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};
