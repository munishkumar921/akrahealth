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
        Schema::create('order_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained();
            $table->foreignUuid('hospital_id')->nullable()->constrained();
            $table->string('orders_category', 255)->nullable();
            $table->string('cpt', 255)->nullable();
            $table->longText('orders_description')->nullable();
            $table->string('snomed', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lists');
    }
};
