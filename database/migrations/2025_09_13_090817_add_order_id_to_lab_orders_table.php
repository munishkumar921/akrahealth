<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds relationship from lab_orders to orders table
     */
    public function up(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            // Add order_id foreign key referencing orders table
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
