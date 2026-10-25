<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventorymaster', function (Blueprint $table) {
            $table->bigIncrements('inventory_id');
            $table->unsignedBigInteger('product_id')->unique();
            $table->integer('quantity_in_stock')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();

            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();

            $table->foreign('product_id')
                ->references('product_id')
                ->on('productmaster')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventorymaster');
    }
};
