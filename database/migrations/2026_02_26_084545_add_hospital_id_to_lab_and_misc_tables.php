<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHospitalIdToLabAndMiscTables extends Migration
{
    public function up()
    {
        $tables = [
            'lab_test_categories',
            'lab_tests',
            'medicines',
            'specialities',
            'bank_accounts',
        ];

        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                if (! Schema::hasColumn($tbl, 'hospital_id')) {
                    $table->char('hospital_id', 36)->nullable()->after('id');
                    $table->foreign('hospital_id')
                        ->references('id')->on('hospitals')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        $tables = [
            'lab_test_categories',
            'lab_tests',
            'medicines',
            'specialities',
            'bank_accounts',
        ];

        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropForeign([$tbl.'_hospital_id_foreign']);
                $table->dropColumn('hospital_id');
            });
        }
    }
}
