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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->index();
            $table->string('mobile')->index()->nullable();
            $table->enum('sex', ['Male', 'Female', 'Other'])->default('Male');
            $table->date('dob')->nullable();
            $table->boolean('marital_status')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignUuid('question_id')->nullable()->constrained();
            $table->text('secret_answer')->nullable();
            $table->rememberToken();
            $table->foreignUuid('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->text('description')->nullable();
            $table->text('certificate')->nullable();
            $table->json('language')->nullable();
            $table->string('specialty_id')->nullable();
            $table->text('experience')->nullable();
            $table->string('cv')->nullable();
            $table->json('skill_id')->nullable();
            $table->string('license')->nullable();
            $table->longText('template')->nullable();
            $table->date('license_date_at')->nullable();
            $table->date('license_expire_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
