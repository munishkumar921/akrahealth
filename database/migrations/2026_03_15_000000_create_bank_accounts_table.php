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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('The admin who created this bank account');
            $table->string('account_holder_name', 255);
            $table->string('bank_name', 255);
            $table->string('account_number', 255);
            $table->string('ifsc_code', 50);
            $table->text('branch_address')->nullable();
            $table->enum('account_type', ['savings', 'current'])->default('savings');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for faster queries
            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
