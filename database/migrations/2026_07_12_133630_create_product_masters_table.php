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
        Schema::create('productmaster', function (Blueprint $table) {

            $table->id('product_id');

            $table->string('product_code', 20);

            $table->string('product_name', 150);

            $table->unsignedBigInteger('category_id');

            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->string('product_internal_code', 50)->nullable();

            $table->string('barcode', 100)->nullable();

            $table->decimal('purchase_price', 10, 2)->default(0.00);

            $table->decimal('selling_price', 10, 2)->default(0.00);

            $table->integer('reorder_level')->default(0);

            $table->enum('status', ['0', '1'])
                ->default('1')
                ->comment('0: Inactive, 1: Active');

            $table->unsignedBigInteger('created_by')->nullable();

            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // Unique Keys
            $table->unique('product_code');
            $table->unique(['created_by', 'product_internal_code']);
            $table->unique(['created_by', 'barcode']);
            $table->unique(['created_by', 'product_name']);

            // Foreign Keys
            $table->foreign('category_id')
                ->references('category_id')
                ->on('categorymaster')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('supplier_id')
                ->references('supplier_id')
                ->on('suppliermaster')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productmaster');
    }
};