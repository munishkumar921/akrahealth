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
        Schema::create('implantable_cardioverter_defibrillators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version')->nullable()->comment('version like: V9 or V10');
            $table->longText('icd')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('common')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('implantable_cardioverter_defibrillators');
    }
};
