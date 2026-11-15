<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceLedgerAndPaymentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'invoice_revisions',
            'invoice_payments',
            'supplier_bill_payments',
            'inventory_movements',
            'document_sequences',
            'invoice_items',
            'invoicemaster',
            'inventorymaster',
            'productmaster',
            'categorymaster',
            'suppliermaster',
            'customermaster',
            'usermaster',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('usermaster', function (Blueprint $table): void {
            $table->bigIncrements('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_gstin')->nullable();
            $table->string('is_admin', 1)->default('N');
            $table->string('status', 1)->default('1');
            $table->boolean('is_jewelry')->default(false);
            $table->string('invoice_template')->default('pdf');
            $table->string('stock_negative_policy')->default('warn_allow');
            $table->timestamps();
        });
        Schema::create('customermaster', function (Blueprint $table): void {
            $table->bigIncrements('customer_id');
            $table->string('customer_code')->unique();
            $table->string('customer_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('suppliermaster', function (Blueprint $table): void {
            $table->bigIncrements('supplier_id');
            $table->string('supplier_name');
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('categorymaster', function (Blueprint $table): void {
            $table->bigIncrements('category_id');
            $table->string('category_name');
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('productmaster', function (Blueprint $table): void {
            $table->bigIncrements('product_id');
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('reorder_level')->default(0);
            $table->decimal('reorder_weight_level', 12, 3)->default(0);
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->decimal('touch', 6, 3)->nullable();
            $table->decimal('default_wastage', 6, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('inventorymaster', function (Blueprint $table): void {
            $table->bigIncrements('inventory_id');
            $table->unsignedBigInteger('product_id')->unique();
            $table->integer('quantity_in_stock')->default(0);
            $table->decimal('weight_in_stock', 12, 3)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('invoicemaster', function (Blueprint $table): void {
            $table->bigIncrements('invoice_id');
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->date('invoice_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->text('notes')->nullable();
            $table->string('customer_name_snapshot')->nullable();
            $table->string('customer_gstin_snapshot')->nullable();
            $table->text('customer_address_snapshot')->nullable();
            $table->string('customer_phone_snapshot')->nullable();
            $table->string('customer_email_snapshot')->nullable();
            $table->string('seller_name_snapshot')->nullable();
            $table->string('seller_gstin_snapshot')->nullable();
            $table->text('seller_address_snapshot')->nullable();
            $table->string('seller_phone_snapshot')->nullable();
            $table->decimal('opening_paid_amount', 12, 2)->nullable();
            $table->boolean('stock_tracked')->default(false);
            $table->string('voucher_section')->nullable();
            $table->decimal('less_kasar', 10, 3)->nullable();
            $table->decimal('rate_cut_weight', 10, 3)->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->decimal('rate_cut_amount', 12, 2)->nullable();
            $table->decimal('other_convert_fine', 10, 3)->nullable();
            $table->decimal('opening_balance', 12, 2)->nullable();
            $table->decimal('opening_gold', 10, 3)->nullable();
            $table->decimal('closing_balance', 12, 2)->nullable();
            $table->decimal('closing_gold', 10, 3)->nullable();
            $table->string('closing_gold_type', 2)->default('Dr');
            $table->text('footer_note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->bigIncrements('item_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('pcs')->nullable();
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->decimal('touch', 6, 3)->nullable();
            $table->decimal('wastage', 6, 3)->nullable();
            $table->decimal('fine_weight', 10, 3)->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('product_name_snapshot')->nullable();
            $table->string('product_code_snapshot')->nullable();
            $table->string('unit_snapshot')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->bigIncrements('document_sequence_id');
            $table->string('document_type', 30);
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('next_sequence')->default(1);
            $table->timestamps();
            $table->unique(['document_type', 'user_id', 'year']);
        });
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->bigIncrements('inventory_movement_id');
            $table->unsignedBigInteger('product_id');
            $table->string('movement_type', 30);
            $table->string('reference_type', 30)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('quantity_delta');
            $table->integer('quantity_after');
            $table->decimal('weight_delta', 12, 3)->nullable();
            $table->decimal('weight_after', 12, 3)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
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
        });
        Schema::create('invoice_revisions', function (Blueprint $table): void {
            $table->bigIncrements('invoice_revision_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedInteger('revision_number');
            $table->json('snapshot');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    protected function tearDown(): void
    {
        foreach ([
            'invoice_revisions',
            'supplier_bill_payments',
            'invoice_payments',
            'inventory_movements',
            'document_sequences',
            'invoice_items',
            'invoicemaster',
            'inventorymaster',
            'productmaster',
            'categorymaster',
            'suppliermaster',
            'customermaster',
            'usermaster',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_invoice_creation_snapshots_details_and_updates_stock_and_revisions_without_changing_existing_routes(): void
    {
        [$userId, $customerId, $productId] = $this->createWorkspace();

        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.createInvoicePost'), $this->invoiceData($customerId, $productId, 2, 10, 2))
            ->assertRedirect(route('invoice.index'));

        $invoiceId = (int) DB::table('invoicemaster')->value('invoice_id');
        $this->assertDatabaseHas('invoicemaster', [
            'invoice_id' => $invoiceId,
            'invoice_number' => 'INV'.now()->year.$userId.'00001',
            'grand_total' => 22,
            'customer_name_snapshot' => 'Customer Before',
            'seller_name_snapshot' => 'Business Before',
            'stock_tracked' => true,
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoiceId,
            'product_name_snapshot' => 'Product Before',
            'product_code_snapshot' => 'SKU-1',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 3,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'reference_type' => 'invoice',
            'reference_id' => $invoiceId,
            'movement_type' => 'sale',
            'quantity_delta' => -2,
            'quantity_after' => 3,
        ]);
        $this->withCookie('GTA', (string) $userId)
            ->get(route('stock.movements', $productId))
            ->assertOk()
            ->assertSee('Stock History')
            ->assertSee('sale');

        DB::table('customermaster')->where('customer_id', $customerId)->update(['customer_name' => 'Customer After']);
        DB::table('productmaster')->where('product_id', $productId)->update(['product_name' => 'Product After']);
        DB::table('usermaster')->where('user_id', $userId)->update(['company_name' => 'Business After']);

        $this->withCookie('GTA', (string) $userId)
            ->get(route('invoice.view', $invoiceId))
            ->assertOk()
            ->assertSee('Customer Before')
            ->assertSee('Product Before')
            ->assertDontSee('Product After');

        $this->withCookie('GTA', (string) $userId)
            ->put(route('invoice.update', $invoiceId), [
                'customer_id' => $customerId,
                'invoice_date' => now()->toDateString(),
                'items' => [[
                    'product_id' => $productId,
                    'quantity' => 3,
                    'unit_price' => '10.00',
                    'tax_amount' => '0.00',
                    'discount_amount' => '0.00',
                ]],
            ])
            ->assertRedirect(route('invoice.index'));

        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 2,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'reference_type' => 'invoice',
            'reference_id' => $invoiceId,
            'movement_type' => 'sale_adjustment',
            'quantity_delta' => -1,
            'quantity_after' => 2,
        ]);
        $this->assertDatabaseHas('invoice_revisions', [
            'invoice_id' => $invoiceId,
            'revision_number' => 1,
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoiceId,
            'product_name_snapshot' => 'Product After',
            'quantity' => 3,
        ]);

        $this->withCookie('GTA', (string) $userId)
            ->delete(route('invoice.delete', $invoiceId))
            ->assertRedirect(route('invoice.index'));

        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 5,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'reference_type' => 'invoice',
            'reference_id' => $invoiceId,
            'movement_type' => 'sale_reversal',
            'quantity_delta' => 3,
            'quantity_after' => 5,
        ]);
        $this->assertDatabaseHas('invoicemaster', [
            'invoice_id' => $invoiceId,
        ]);
        $this->assertNotNull(DB::table('invoicemaster')->where('invoice_id', $invoiceId)->value('deleted_at'));

        DB::table('customermaster')->where('customer_id', $customerId)->update(['deleted_at' => now()]);
        DB::table('productmaster')->where('product_id', $productId)->update(['deleted_at' => now()]);
        $dashboard = $this->withCookie('GTA', (string) $userId)
            ->get(route('dashboard'))
            ->assertOk();
        $this->assertSame(0, $dashboard->viewData('kpis')['invoices_total']);
        $this->assertSame(0, $dashboard->viewData('kpis')['customers_total']);
        $this->assertSame(0, $dashboard->viewData('kpis')['products_total']);
    }

    public function test_invoice_payments_update_status_reject_overpayment_and_can_be_voided_with_reason(): void
    {
        [$userId, $customerId, $productId] = $this->createWorkspace();
        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.createInvoicePost'), $this->invoiceData($customerId, $productId, 1, 100, 0))
            ->assertRedirect(route('invoice.index'));
        $invoiceId = (int) DB::table('invoicemaster')->value('invoice_id');

        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.payments.store', $invoiceId), [
                'amount' => '40.00',
                'paid_at' => now()->toDateString(),
                'method' => 'upi',
                'reference_number' => 'UPI-001',
            ])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoicemaster', ['invoice_id' => $invoiceId, 'payment_status' => 'partial']);

        $this->withCookie('GTA', (string) $userId)
            ->from(route('invoice.view', $invoiceId))
            ->post(route('invoice.payments.store', $invoiceId), [
                'amount' => '61.00',
                'paid_at' => now()->toDateString(),
                'method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.payments.store', $invoiceId), [
                'amount' => '60.00',
                'paid_at' => now()->toDateString(),
                'method' => 'cash',
            ])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoicemaster', ['invoice_id' => $invoiceId, 'payment_status' => 'paid']);

        $paymentId = (int) DB::table('invoice_payments')->orderByDesc('invoice_payment_id')->value('invoice_payment_id');
        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.payments.void', [$invoiceId, $paymentId]), ['void_reason' => 'Duplicate receipt entry'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoice_payments', [
            'invoice_payment_id' => $paymentId,
            'void_reason' => 'Duplicate receipt entry',
        ]);
        $this->assertDatabaseHas('invoicemaster', ['invoice_id' => $invoiceId, 'payment_status' => 'partial']);

        $dashboard = $this->withCookie('GTA', (string) $userId)
            ->get(route('dashboard'))
            ->assertOk();
        $this->assertEquals(60, (float) $dashboard->viewData('kpis')['outstanding_total']);
        $this->assertEquals(40, (float) $dashboard->viewData('kpis')['collected_total']);
    }

    public function test_legacy_partial_invoice_requires_an_explicit_opening_balance_before_tracking_new_payments(): void
    {
        [$userId, $customerId] = $this->createWorkspace();
        $invoiceId = DB::table('invoicemaster')->insertGetId([
            'invoice_number' => 'LEGACY-'.$userId,
            'customer_id' => $customerId,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100,
            'total_tax' => 0,
            'discount_amount' => 0,
            'grand_total' => 100,
            'payment_status' => 'partial',
            'opening_paid_amount' => null,
            'stock_tracked' => false,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withCookie('GTA', (string) $userId)
            ->from(route('invoice.view', $invoiceId))
            ->post(route('invoice.payments.store', $invoiceId), [
                'amount' => '10.00',
                'paid_at' => now()->toDateString(),
                'method' => 'cash',
            ])
            ->assertSessionHasErrors('opening_paid_amount');

        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.payments.store', $invoiceId), [
                'opening_paid_amount' => '40.00',
                'amount' => '10.00',
                'paid_at' => now()->toDateString(),
                'method' => 'cash',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invoicemaster', [
            'invoice_id' => $invoiceId,
            'opening_paid_amount' => 40,
            'payment_status' => 'partial',
        ]);
    }

    public function test_jewellery_stock_uses_net_grams_and_invoice_gold_carries_forward_per_customer(): void
    {
        [$userId, $customerId, $productId] = $this->createWorkspace();
        DB::table('usermaster')->where('user_id', $userId)->update([
            'is_jewelry' => true,
            'stock_negative_policy' => 'block',
        ]);
        DB::table('inventorymaster')->where('product_id', $productId)->update([
            'weight_in_stock' => 10,
        ]);

        $voucherData = [
            'invoice_number' => 'IGNORED',
            'customer_id' => $customerId,
            'invoice_date' => now()->toDateString(),
            'voucher_section' => 'SALES',
            'opening_gold' => '15.000',
            'items' => [[
                'product_id' => $productId,
                'pcs' => 40,
                'gross_weight' => '2.100',
                'net_weight' => '2.000',
                'touch' => '95.000',
                'wastage' => '2.500',
            ]],
        ];

        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.createInvoicePost'), $voucherData)
            ->assertRedirect(route('invoice.index'));

        $firstInvoiceId = (int) DB::table('invoicemaster')->value('invoice_id');
        $this->assertDatabaseHas('invoicemaster', [
            'invoice_id' => $firstInvoiceId,
            'opening_gold' => 15,
            'closing_gold' => 16.95,
        ]);
        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 5,
            'weight_in_stock' => 8,
        ]);

        $this->withCookie('GTA', (string) $userId)
            ->get(route('invoice.create'))
            ->assertOk()
            ->assertSee('data-gold-balance="16.950"', false);

        $voucherData['opening_gold'] = '0.000';
        $voucherData['items'][0]['net_weight'] = '1.000';
        $voucherData['items'][0]['gross_weight'] = '1.100';
        $voucherData['items'][0]['wastage'] = '0.000';
        $voucherData['closing_gold'] = '21.000';
        $this->withCookie('GTA', (string) $userId)
            ->post(route('invoice.createInvoicePost'), $voucherData)
            ->assertRedirect(route('invoice.index'));

        $this->assertDatabaseHas('invoicemaster', [
            'invoice_id' => $firstInvoiceId + 1,
            'opening_gold' => 16.95,
            'closing_gold' => 21,
        ]);

        $voucherData['items'][0]['net_weight'] = '100.000';
        $voucherData['items'][0]['gross_weight'] = '100.100';
        $this->withCookie('GTA', (string) $userId)
            ->from(route('invoice.create'))
            ->post(route('invoice.createInvoicePost'), $voucherData)
            ->assertSessionHasErrors('stock');

        $this->assertSame(2, DB::table('invoicemaster')->count());
    }

    private function createWorkspace(): array
    {
        $now = now();
        $userId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Workspace Owner',
            'email' => uniqid('owner').'@example.com',
            'password' => 'unused',
            'company_name' => 'Business Before',
            'company_address' => 'Original business address',
            'company_gstin' => '22AAAAA0000A1Z5',
            'is_admin' => 'N',
            'status' => '1',
            'is_jewelry' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $customerId = DB::table('customermaster')->insertGetId([
            'customer_code' => uniqid('customer'),
            'customer_name' => 'Customer Before',
            'email' => 'customer@example.com',
            'phone' => '9999999999',
            'gst_number' => '27AAAAA0000A1Z5',
            'billing_address' => 'Customer address before',
            'status' => '1',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $categoryId = DB::table('categorymaster')->insertGetId([
            'category_name' => 'Test Category',
            'status' => '1',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('productmaster')->insertGetId([
            'product_code' => 'SKU-1',
            'product_name' => 'Product Before',
            'category_id' => $categoryId,
            'purchase_price' => 5,
            'selling_price' => 10,
            'reorder_level' => 1,
            'status' => '1',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('inventorymaster')->insert([
            'product_id' => $productId,
            'quantity_in_stock' => 5,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('suppliermaster')->insert([
            'supplier_name' => 'Test Supplier',
            'status' => '1',
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$userId, $customerId, $productId];
    }

    private function invoiceData(int $customerId, int $productId, int $quantity, float $unitPrice, float $taxAmount): array
    {
        return [
            'invoice_number' => 'PREVIEW-NUMBER',
            'customer_id' => $customerId,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 0,
            'total_tax' => 0,
            'discount_amount' => 0,
            'grand_total' => 0,
            'items' => [[
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'tax_amount' => number_format($taxAmount, 2, '.', ''),
                'discount_amount' => '0.00',
            ]],
        ];
    }
}
