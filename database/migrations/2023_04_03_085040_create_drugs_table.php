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
        Schema::create('drugs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained();
            $table->string('product_ndc')->nullable();
            $table->string('generic_name')->index();
            $table->string('labeler_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->text('active_ingredients')->nullable();
            $table->string('packaging')->nullable();
            $table->string('listing_expiration_date')->nullable();
            $table->longText('openfda')->nullable();
            $table->string('marketing_category')->nullable()->index();
            $table->string('dosage_form')->nullable();
            $table->string('spl_id')->nullable();
            $table->string('product_type')->nullable();
            $table->string('route')->nullable();
            $table->string('marketing_start_date')->nullable();
            $table->string('application_number')->nullable();
            $table->text('brand_name_base')->nullable();
            $table->longText('pharm_class')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('logo')->nullable();
            $table->string('price')->nullable();
            $table->string('discounted_price')->nullable();
            $table->string('discount')->nullable();
            $table->integer('view_count')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
