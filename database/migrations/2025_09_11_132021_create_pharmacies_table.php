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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignUuid('state_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('license_number');
            $table->text('address');
            $table->text('city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('opening_time')->nullable();
            $table->string('closing_time')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('banner')->nullable();
            $table->text('about')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('license')->nullable();
            $table->string('gst_license')->nullable();
            $table->string('store_front_photo')->nullable();
            $table->string('owner_id_proof')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
