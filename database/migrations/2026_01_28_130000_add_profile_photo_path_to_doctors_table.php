<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilePhotoPathToDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('doctors', 'profile_photo_path')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->string('profile_photo_path')->nullable()->after('whatsapp_consultation');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('doctors', 'profile_photo_path')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('profile_photo_path');
            });
        }
    }
}
