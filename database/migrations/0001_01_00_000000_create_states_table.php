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
        Schema::create('states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('iso2')->nullable();
            $table->string('code')->nullable();
            $table->string('capital')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
