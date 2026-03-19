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
        Schema::create('vaccines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('date_purchase')->nullable();
            $table->foreignUuid('immunization_id')->nullable()->constrained();
            $table->foreignUuid('current_procedural_terminology_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->bigInteger('quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccines');
    }
};
