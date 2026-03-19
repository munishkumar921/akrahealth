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
        Schema::create('icd9s', function (Blueprint $table) {
            $table->increments('icd9_id');
            $table->string('icd9', 255)->nullable();
            $table->string('icd9_description', 255)->nullable();
            $table->boolean('icd9_common')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icd9s');
    }
};
