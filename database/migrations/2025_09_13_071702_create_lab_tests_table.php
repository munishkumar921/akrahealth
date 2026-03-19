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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /* Ownership */
            $table->foreignUuid('lab_test_category_id')->constrained('lab_test_categories');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            /* Detail */
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sample_type')->nullable()->comment('e.g., blood, urine, imaging');
            $table->boolean('fasting_required')->default(false);
            $table->string('report_time')->nullable()->comment('Time taken by the lab to process this test. e.g., 8 hours or 5 days');
            $table->text('instructions')->nullable();

            /* Pricing */
            $table->decimal('price', 10, 2)->default(0)->nullable();
            $table->decimal('discount', 10, 2)->default(0)->nullable();
            $table->decimal('final_price', 10, 2)->default(0)->nullable();
            $table->string('currency', 10)->default('INR');

            /* Flags */
            $table->boolean('is_home_collection_available')->default(false);
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
