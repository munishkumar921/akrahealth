<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->longText('form')->nullable();     // form title or identifier
            $table->timestamps();
            $table->softDeletes(); // optional: enable soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_forms');
    }
};
