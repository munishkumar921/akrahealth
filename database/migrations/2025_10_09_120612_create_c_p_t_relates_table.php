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
        Schema::create('c_p_t_relates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->nullable();
            $table->string('cpt', 255)->nullable();
            $table->longText('cpt_description')->nullable();
            $table->string('cpt_charge', 255)->nullable();
            $table->boolean('favorite')->nullable();
            $table->integer('unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_p_t_relates');
    }
};
