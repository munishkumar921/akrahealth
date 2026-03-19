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
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('specialty', 100)->nullable();
            $table->string('displayname', 255)->nullable();
            $table->string('lastname', 100)->nullable();
            $table->string('firstname', 100)->nullable();
            $table->string('facility', 100)->nullable();
            $table->string('prefix', 100)->nullable();
            $table->string('suffix', 100)->nullable();
            $table->string('street_address1', 255)->nullable();
            $table->string('street_address2', 255)->nullable();
            $table->string('insurance_plan_payor_id', 255)->nullable();
            $table->string('insurance_plan_type', 255)->nullable();
            $table->string('insurance_plan_assignment', 4)->nullable();
            $table->string('insurance_plan_ppa_phone', 255)->nullable();
            $table->string('insurance_plan_ppa_fax', 255)->nullable();
            $table->string('insurance_plan_ppa_url', 255)->nullable();
            $table->string('insurance_plan_mpa_phone', 255)->nullable();
            $table->string('insurance_plan_mpa_fax', 255)->nullable();
            $table->string('insurance_plan_mpa_url', 255)->nullable();
            $table->string('ordering_id', 255)->nullable();
            $table->string('insurance_box_31', 4)->nullable();
            $table->string('insurance_box_32a', 4)->nullable();
            $table->string('npi', 255)->nullable();
            $table->string('electronic_order', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            //
        });
    }
};
