<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoicemaster')) {
            Schema::create('invoicemaster', function (Blueprint $table): void {
                $table->bigIncrements('invoice_id');
                $table->string('invoice_number', 50)->unique();
                $table->unsignedBigInteger('customer_id');
                $table->date('invoice_date');
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

                $table->index(['created_by', 'invoice_date']);
                $table->foreign('customer_id')
                    ->references('customer_id')
                    ->on('customermaster')
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
        }

        if (! Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table): void {
                $table->bigIncrements('item_id');
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->string('unit', 20)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['invoice_id', 'deleted_at']);
                $table->foreign('invoice_id')
                    ->references('invoice_id')
                    ->on('invoicemaster')
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
    }

    public function down(): void
    {
        // These tables may predate migrations in already-installed workspaces.
    }
};
