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
        Schema::create('physical_examinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained();
            $table->foreignUuid('patient_id')->nullable()->constrained();
            $table->string('encounter_provider', 255)->nullable();
            $table->timestamp('pe_date')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->longText('pe_gen1')->nullable();
            $table->longText('pe_eye1')->nullable();
            $table->longText('pe_eye2')->nullable();
            $table->longText('pe_eye3')->nullable();
            $table->longText('pe_ent1')->nullable();
            $table->longText('pe_ent2')->nullable();
            $table->longText('pe_ent3')->nullable();
            $table->longText('pe_ent4')->nullable();
            $table->longText('pe_ent5')->nullable();
            $table->longText('pe_ent6')->nullable();
            $table->longText('pe_neck1')->nullable();
            $table->longText('pe_neck2')->nullable();
            $table->longText('pe_resp1')->nullable();
            $table->longText('pe_resp2')->nullable();
            $table->longText('pe_resp3')->nullable();
            $table->longText('pe_resp4')->nullable();
            $table->longText('pe_cv1')->nullable();
            $table->longText('pe_cv2')->nullable();
            $table->longText('pe_cv3')->nullable();
            $table->longText('pe_cv4')->nullable();
            $table->longText('pe_cv5')->nullable();
            $table->longText('pe_cv6')->nullable();
            $table->longText('pe_ch1')->nullable();
            $table->longText('pe_ch2')->nullable();
            $table->longText('pe_gi1')->nullable();
            $table->longText('pe_gi2')->nullable();
            $table->longText('pe_gi3')->nullable();
            $table->longText('pe_gi4')->nullable();
            $table->longText('pe_gu1')->nullable();
            $table->longText('pe_gu2')->nullable();
            $table->longText('pe_gu3')->nullable();
            $table->longText('pe_gu4')->nullable();
            $table->longText('pe_gu5')->nullable();
            $table->longText('pe_gu6')->nullable();
            $table->longText('pe_gu7')->nullable();
            $table->longText('pe_gu8')->nullable();
            $table->longText('pe_gu9')->nullable();
            $table->longText('pe_lymph1')->nullable();
            $table->longText('pe_lymph2')->nullable();
            $table->longText('pe_lymph3')->nullable();
            $table->longText('pe_ms1')->nullable();
            $table->longText('pe_ms2')->nullable();
            $table->longText('pe_ms3')->nullable();
            $table->longText('pe_ms4')->nullable();
            $table->longText('pe_ms5')->nullable();
            $table->longText('pe_ms6')->nullable();
            $table->longText('pe_ms7')->nullable();
            $table->longText('pe_ms8')->nullable();
            $table->longText('pe_ms9')->nullable();
            $table->longText('pe_ms10')->nullable();
            $table->longText('pe_ms11')->nullable();
            $table->longText('pe_ms12')->nullable();
            $table->longText('pe_skin1')->nullable();
            $table->longText('pe_skin2')->nullable();
            $table->longText('pe_neuro1')->nullable();
            $table->longText('pe_neuro2')->nullable();
            $table->longText('pe_neuro3')->nullable();
            $table->longText('pe_psych1')->nullable();
            $table->longText('pe_psych2')->nullable();
            $table->longText('pe_psych3')->nullable();
            $table->longText('pe_psych4')->nullable();
            $table->longText('pe')->nullable();
            $table->longText('pe_constitutional1')->nullable();
            $table->longText('pe_mental1')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_examinations');
    }
};
