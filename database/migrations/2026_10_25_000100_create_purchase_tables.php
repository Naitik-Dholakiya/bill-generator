<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchasemaster', function (Blueprint $table) {
            $table->bigIncrements('purchase_id');
            $table->string('purchase_number', 50)->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('purchase_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_by', 'purchase_date']);
            $table->foreign('supplier_id')
                ->references('supplier_id')
                ->on('suppliermaster')
                ->restrictOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
            $table->foreign('updated_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->bigIncrements('purchase_item_id');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_id', 'deleted_at']);
            $table->foreign('purchase_id')
                ->references('purchase_id')
                ->on('purchasemaster')
                ->cascadeOnDelete();
            $table->foreign('product_id')
                ->references('product_id')
                ->on('productmaster')
                ->restrictOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchasemaster');
    }
};
