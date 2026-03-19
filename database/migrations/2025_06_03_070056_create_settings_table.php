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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();

            $table->enum('type', [
                'string',
                'text',
                'boolean',
                'integer',
                'decimal',
                'json',
                'file',
                'encrypted',
            ])->default('string');

            $table->string('description')->nullable();
            $table->string('group')->default('general');

            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
