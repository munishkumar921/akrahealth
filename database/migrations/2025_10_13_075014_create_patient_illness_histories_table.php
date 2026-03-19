<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_illness_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->constrained();
            $table->longText('hpi')->nullable();
            $table->longText('forms')->nullable();
            $table->longText('situation')->nullable();
            $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('date of history of patient illness');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_illness_histories');
    }
};
