<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration fixes the data truncation issue where order_id column
     * was defined as unsignedBigInteger but orders table uses UUID as primary key.
     */
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            // Drop the existing unsignedBigInteger order_id column
            $table->dropColumn('order_id');
        });

        Schema::table('alerts', function (Blueprint $table) {
            // Add the order_id column as uuid type to match orders table primary key
            $table->uuid('order_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable();
        });
    }
};
