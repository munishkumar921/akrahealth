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
        Schema::table('labs', function (Blueprint $table) {
            // Add address_id foreign key column if it doesn't exist
            if (! Schema::hasColumn('labs', 'address_id')) {
                $table->foreignUuid('address_id')->nullable()->constrained('addresses')->onDelete('set null');
            }

            // Drop address fields only if they exist
            if (Schema::hasColumn('labs', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('labs', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('labs', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('labs', 'zip')) {
                $table->dropColumn('zip');
            }
            if (Schema::hasColumn('labs', 'country')) {
                $table->dropColumn('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            // Restore address fields
            if (! Schema::hasColumn('labs', 'address')) {
                $table->string('address')->nullable();
            }
            if (! Schema::hasColumn('labs', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('labs', 'state')) {
                $table->string('state')->nullable();
            }
            if (! Schema::hasColumn('labs', 'zip')) {
                $table->string('zip')->nullable();
            }
            if (! Schema::hasColumn('labs', 'country')) {
                $table->string('country')->nullable();
            }

            // Drop address_id foreign key
            if (Schema::hasColumn('labs', 'address_id')) {
                $table->dropForeign(['address_id']);
                $table->dropColumn('address_id');
            }
        });
    }
};
