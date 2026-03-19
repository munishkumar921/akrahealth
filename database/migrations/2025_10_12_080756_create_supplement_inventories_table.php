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
        Schema::create('supplement_inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->nullable();
            $table->dateTime('date_purchase')->nullable();
            $table->longText('sup_description')->nullable();
            $table->string('sup_strength', 255)->nullable();
            $table->string('sup_manufacturer', 255)->nullable();
            $table->dateTime('sup_expiration')->nullable();
            $table->string('cpt', 255)->nullable();
            $table->string('charge', 255)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('sup_lot', 255)->nullable();
            $table->string('quantity1', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplement_inventories');
    }
};
