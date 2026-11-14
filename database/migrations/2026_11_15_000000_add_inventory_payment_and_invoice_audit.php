<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoicemaster', function (Blueprint $table): void {
            $table->string('customer_name_snapshot')->nullable();
            $table->string('customer_gstin_snapshot', 15)->nullable();
            $table->text('customer_address_snapshot')->nullable();
            $table->string('customer_phone_snapshot', 20)->nullable();
            $table->string('customer_email_snapshot', 100)->nullable();
            $table->string('seller_name_snapshot', 150)->nullable();
            $table->string('seller_gstin_snapshot', 15)->nullable();
            $table->text('seller_address_snapshot')->nullable();
            $table->string('seller_phone_snapshot', 20)->nullable();
            $table->decimal('opening_paid_amount', 12, 2)->nullable();
            $table->boolean('stock_tracked')->default(false);
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->string('product_name_snapshot', 150)->nullable();
            $table->string('product_code_snapshot', 50)->nullable();
            $table->string('unit_snapshot', 20)->nullable();
        });

        Schema::table('purchasemaster', function (Blueprint $table): void {
            $table->decimal('opening_paid_amount', 12, 2)->nullable();
        });

        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->bigIncrements('document_sequence_id');
            $table->string('document_type', 30);
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('next_sequence')->default(1);
            $table->timestamps();

            $table->unique(['document_type', 'user_id', 'year'], 'document_sequences_type_user_year_unique');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('usermaster')
                ->cascadeOnDelete();
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->bigIncrements('inventory_movement_id');
            $table->unsignedBigInteger('product_id');
            $table->string('movement_type', 30);
            $table->string('reference_type', 30)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('quantity_delta');
            $table->integer('quantity_after');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at'], 'inventory_movements_product_date_index');
            $table->index(['reference_type', 'reference_id'], 'inventory_movements_reference_index');
            $table->foreign('product_id')
                ->references('product_id')
                ->on('productmaster')
                ->restrictOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });

        Schema::create('invoice_payments', function (Blueprint $table): void {
            $table->bigIncrements('invoice_payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('method', 30);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'voided_at', 'paid_at'], 'invoice_payments_invoice_date_index');
            $table->foreign('invoice_id')
                ->references('invoice_id')
                ->on('invoicemaster')
                ->restrictOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->restrictOnDelete();
            $table->foreign('voided_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });

        Schema::create('supplier_bill_payments', function (Blueprint $table): void {
            $table->bigIncrements('supplier_bill_payment_id');
            $table->unsignedBigInteger('purchase_id');
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('method', 30);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['purchase_id', 'voided_at', 'paid_at'], 'supplier_bill_payments_bill_date_index');
            $table->foreign('purchase_id')
                ->references('purchase_id')
                ->on('purchasemaster')
                ->restrictOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->restrictOnDelete();
            $table->foreign('voided_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });

        Schema::create('invoice_revisions', function (Blueprint $table): void {
            $table->bigIncrements('invoice_revision_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedInteger('revision_number');
            $table->json('snapshot');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['invoice_id', 'revision_number'], 'invoice_revisions_invoice_revision_unique');
            $table->foreign('invoice_id')
                ->references('invoice_id')
                ->on('invoicemaster')
                ->cascadeOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });

        $this->backfillInvoiceSnapshots();
        $this->backfillProductSnapshots();
        $this->recordOpeningStock();

        DB::table('invoicemaster')
            ->where('payment_status', 'paid')
            ->update(['opening_paid_amount' => DB::raw('grand_total')]);
        DB::table('invoicemaster')
            ->where('payment_status', 'pending')
            ->update(['opening_paid_amount' => 0]);
        DB::table('purchasemaster')
            ->where('payment_status', 'paid')
            ->update(['opening_paid_amount' => DB::raw('grand_total')]);
        DB::table('purchasemaster')
            ->where('payment_status', 'pending')
            ->update(['opening_paid_amount' => 0]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_revisions');
        Schema::dropIfExists('supplier_bill_payments');
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('document_sequences');

        Schema::table('purchasemaster', function (Blueprint $table): void {
            $table->dropColumn('opening_paid_amount');
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->dropColumn([
                'product_name_snapshot',
                'product_code_snapshot',
                'unit_snapshot',
            ]);
        });

        Schema::table('invoicemaster', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_name_snapshot',
                'customer_gstin_snapshot',
                'customer_address_snapshot',
                'customer_phone_snapshot',
                'customer_email_snapshot',
                'seller_name_snapshot',
                'seller_gstin_snapshot',
                'seller_address_snapshot',
                'seller_phone_snapshot',
                'opening_paid_amount',
                'stock_tracked',
            ]);
        });
    }

    private function backfillInvoiceSnapshots(): void
    {
        DB::table('invoicemaster as i')
            ->leftJoin('customermaster as c', 'c.customer_id', '=', 'i.customer_id')
            ->leftJoin('usermaster as u', 'u.user_id', '=', 'i.created_by')
            ->select([
                'i.invoice_id',
                'c.customer_name',
                'c.gst_number as customer_gstin',
                'c.billing_address as customer_address',
                'c.phone as customer_phone',
                'c.email as customer_email',
                'u.company_name',
                'u.full_name',
                'u.company_gstin',
                'u.company_address',
                'u.phone as seller_phone',
            ])
            ->orderBy('i.invoice_id')
            ->chunkById(500, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    DB::table('invoicemaster')
                        ->where('invoice_id', $invoice->invoice_id)
                        ->update([
                            'customer_name_snapshot' => $invoice->customer_name,
                            'customer_gstin_snapshot' => $invoice->customer_gstin,
                            'customer_address_snapshot' => $invoice->customer_address,
                            'customer_phone_snapshot' => $invoice->customer_phone,
                            'customer_email_snapshot' => $invoice->customer_email,
                            'seller_name_snapshot' => $invoice->company_name ?: $invoice->full_name,
                            'seller_gstin_snapshot' => $invoice->company_gstin,
                            'seller_address_snapshot' => $invoice->company_address,
                            'seller_phone_snapshot' => $invoice->seller_phone,
                        ]);
                }
            }, 'i.invoice_id', 'invoice_id');
    }

    private function backfillProductSnapshots(): void
    {
        DB::table('invoice_items as items')
            ->leftJoin('productmaster as products', 'products.product_id', '=', 'items.product_id')
            ->select([
                'items.item_id',
                'products.product_name',
                'products.product_code',
            ])
            ->orderBy('items.item_id')
            ->chunkById(500, function ($items): void {
                foreach ($items as $item) {
                    DB::table('invoice_items')
                        ->where('item_id', $item->item_id)
                        ->update([
                            'product_name_snapshot' => $item->product_name,
                            'product_code_snapshot' => $item->product_code,
                        ]);
                }
            }, 'items.item_id', 'item_id');
    }

    private function recordOpeningStock(): void
    {
        DB::table('inventorymaster')
            ->whereNull('deleted_at')
            ->orderBy('inventory_id')
            ->chunkById(500, function ($inventories): void {
                $now = now();
                $movements = [];

                foreach ($inventories as $inventory) {
                    $movements[] = [
                        'product_id' => $inventory->product_id,
                        'movement_type' => 'opening',
                        'reference_type' => 'inventory',
                        'reference_id' => $inventory->inventory_id,
                        'quantity_delta' => $inventory->quantity_in_stock,
                        'quantity_after' => $inventory->quantity_in_stock,
                        'notes' => 'Opening stock balance captured when inventory movement tracking was enabled.',
                        'created_by' => $inventory->created_by,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($movements !== []) {
                    DB::table('inventory_movements')->insert($movements);
                }
            }, 'inventory_id', 'inventory_id');
    }
};
