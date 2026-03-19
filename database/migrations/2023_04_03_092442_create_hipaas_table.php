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
        Schema::create('hipaas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained();
            $table->foreignUuid('doctor_id')->constrained();
            $table->foreignUuid('document_id')->constrained();
            $table->foreignUuid('hospital_id')->constrained();
            $table->foreignUuid('address_id')->nullable();
            $table->date('date_release');
            $table->string('reason')->nullable();
            $table->bigInteger('eid')->nullable();
            $table->string('role')->nullable();
            // $table->text('tags')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hipaas');
    }
};
