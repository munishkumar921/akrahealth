<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration fixes foreign key constraints in the orders table
     * by dropping the old address_id foreign key if it exists.
     */
    public function up(): void
    {
        // Check if address_id column exists
        if (Schema::hasColumn('orders', 'address_id')) {
            // Check if the foreign key constraint exists before trying to drop it
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_NAME = 'orders'
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME LIKE '%address_id%'
            ");

            if (! empty($foreignKeys)) {
                try {
                    DB::statement('ALTER TABLE orders DROP FOREIGN KEY '.$foreignKeys[0]->CONSTRAINT_NAME);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to recreate the old foreign key as we already have lab_id working
    }
};
