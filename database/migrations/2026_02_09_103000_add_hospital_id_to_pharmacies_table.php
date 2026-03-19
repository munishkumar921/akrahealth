<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->foreignUuid('hospital_id')
                ->nullable()
                ->after('user_id')
                ->constrained('hospitals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropColumn('hospital_id');
        });
    }
};
