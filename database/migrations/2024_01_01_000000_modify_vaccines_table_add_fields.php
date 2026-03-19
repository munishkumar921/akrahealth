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
        Schema::table('vaccines', function (Blueprint $table) {
            // Remove existing foreign key columns
            $table->dropForeign(['immunization_id']);
            $table->dropForeign(['current_procedural_terminology_id']);
            $table->dropColumn(['immunization_id', 'current_procedural_terminology_id']);

            // Add new columns
            $table->string('immunization')->nullable()->after('id');
            $table->string('lot')->nullable()->after('date_purchase');
            $table->string('manufacturer')->nullable()->after('lot');
            $table->date('expiration')->nullable()->after('manufacturer');
            $table->string('brand')->nullable()->after('expiration');
            $table->string('cpt')->nullable()->after('brand');
            $table->string('code')->nullable()->after('cpt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['immunization', 'lot', 'manufacturer', 'expiration', 'brand', 'cpt', 'code']);

            // Restore original columns
            $table->foreignUuid('immunization_id')->nullable()->constrained();
            $table->foreignUuid('current_procedural_terminology_id')->constrained();
        });
    }
};
