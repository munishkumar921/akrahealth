<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('reminder_method')->nullable();
            $table->string('reminder_to')->nullable();
            $table->string('reminder_interval')->nullable();
            $table->date('date')->nullable();

        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('reminder_method');
            $table->dropColumn('reminder_to');
            $table->dropColumn('reminder_interval');
            $table->dropColumn('date');
        });
    }
};
