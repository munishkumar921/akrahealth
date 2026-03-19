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
        Schema::create('hospital_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->foreignUuid('current_procedural_terminology_id')->constrained();
            $table->string('type')->nullable();
            $table->longText('description')->nullable();
            $table->longText('complications')->nullable();
            $table->longText('ebl')->nullable()->comment('estimated blood loss');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_procedures');
    }
};
