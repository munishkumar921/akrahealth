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
        Schema::create('medicines', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /* Medicine Info */
            $table->string('name')->index();
            $table->string('brand_name')->index()->nullable();
            $table->string('generic_name')->index()->nullable();
            $table->text('composition')->nullable();
            $table->string('type')->nullable()->comment('e.g, allopathy');
            $table->enum('dosage_form', [
                'tablet',
                'capsule',
                'syrup',
                'injection',
                'ointment',
                'spray',
                'drop',
                'powder',
                'gel',
            ])->nullable();
            $table->string('strength')->nullable();
            $table->enum('route', [
                'oral',
                'topical',
                'intravenous',
                'intramuscular',
                'sublingual',
                'nasal',
                'rectal',
                'inhalation',
            ])->nullable();

            /* Usage */
            $table->text('indications')->nullable()->comment('Encrypted');
            $table->text('contraindications')->nullable()->comment('Encrypted');
            $table->text('side_effects')->nullable()->comment('Encrypted');
            $table->text('precautions')->nullable()->comment('Encrypted');
            $table->text('instructions')->nullable()->comment('Encrypted');

            /* Pricing */
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('INR');

            /* Inventory */
            $table->integer('stock_quantity')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('batch_no')->nullable();

            /* Flags */
            $table->boolean('is_prescription_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_encrypted')->default(true);

            /* Timestamps */
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
