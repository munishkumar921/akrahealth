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
        Schema::create('umas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resource_set_id')->nullable();
            $table->longText('scope')->nullable();
            $table->longText('user_access_policy_uri')->nullable();
            $table->integer('table_id')->nullable();
            $table->string('table')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umas');
    }
};
