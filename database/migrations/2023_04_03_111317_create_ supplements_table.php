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
        Schema::create('supplements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained();
            $table->foreignUuid('current_procedural_terminology_id')->constrained();
            $table->timestamp('purchase_date')->nullable();
            $table->longText('description')->nullable();
            $table->string('strength')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('expiration')->nullable();
            $table->string('charge')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->string('sup_lot')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplements');
    }
};
