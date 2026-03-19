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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('plan_for'); // Doctor or Hospital
            $table->string('title');
            $table->string('frequency'); // Monthly or Yearly
            $table->json('permissions')->nullable(); // Array of permissions
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->longText('features')->nullable(); // Array of features
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
