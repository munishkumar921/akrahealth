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
        Schema::table('hospital_timings', function (Blueprint $table) {
            $table->boolean('weekends')->nullable()->after('hospital_id');
            $table->string('time_zone')->nullable()->after('weekends');
            $table->time('default_open_time')->nullable()->after('time_zone');
            $table->time('default_close_time')->nullable()->after('default_open_time');
            $table->string('day_of_week')->nullable()->after('default_close_time');
            $table->time('open_time')->nullable()->after('day_of_week');
            $table->time('close_time')->nullable()->after('open_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospital_timings', function (Blueprint $table) {
            $table->dropColumn([
                'weekends',
                'time_zone',
                'default_open_time',
                'default_close_time',
                'day_of_week',
                'open_time',
                'close_time',
            ]);
        });
    }
};
