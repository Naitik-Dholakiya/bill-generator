<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JewellerySupplierBillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['purchase_items', 'purchasemaster', 'inventorymaster', 'productmaster', 'categorymaster', 'suppliermaster', 'usermaster'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('usermaster', function (Blueprint $table): void {
            $table->bigIncrements('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('is_admin', 1)->default('N');
            $table->string('status', 1)->default('1');
            $table->boolean('is_jewelry')->default(false);
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_gstin')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        Schema::create('suppliermaster', function (Blueprint $table): void {
            $table->bigIncrements('supplier_id');
            $table->string('supplier_code')->unique();
            $table->string('supplier_name');
            $table->text('supplier_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('categorymaster', function (Blueprint $table): void {
            $table->bigIncrements('category_id');
            $table->string('category_name');
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
        Schema::create('productmaster', function (Blueprint $table): void {
            $table->bigIncrements('product_id');
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->string('status', 1)->default('1');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->decimal('touch', 6, 3)->nullable();
            $table->decimal('default_wastage', 6, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('purchasemaster', function (Blueprint $table): void {
            $table->bigIncrements('purchase_id');
            $table->string('purchase_number')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('purchase_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('payment_status');
            $table->text('notes')->nullable();
            $table->string('voucher_section')->nullable();
            $table->decimal('less_kasar', 10, 3)->nullable();
            $table->decimal('rate_cut_weight', 10, 3)->nullable();
            $table->decimal('other_convert_fine', 10, 3)->nullable();
            $table->decimal('opening_gold', 10, 3)->nullable();
            $table->decimal('closing_gold', 10, 3)->nullable();
            $table->text('footer_note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->bigIncrements('purchase_item_id');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('pcs')->nullable();
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->decimal('touch', 6, 3)->nullable();
            $table->decimal('wastage', 6, 3)->nullable();
            $table->decimal('fine_weight', 10, 3)->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('inventorymaster', function (Blueprint $table): void {
            $table->bigIncrements('inventory_id');
            $table->unsignedBigInteger('product_id')->unique();
            $table->integer('quantity_in_stock')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        foreach (['purchase_items', 'purchasemaster', 'inventorymaster', 'productmaster', 'categorymaster', 'suppliermaster', 'usermaster'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_jewellery_supplier_bill_saves_fine_weight_and_piece_stock(): void
    {
        [$userId, $supplierId, $productId] = $this->createWorkspace(true);

        $this->withCookie('GTA', (string) $userId)
            ->get(route('supplier-bills.create'))
            ->assertOk()
            ->assertSee('Fine wt (g)')
            ->assertDontSee('Unit Cost');

        $this->withCookie('GTA', (string) $userId)
            ->post(route('supplier-bills.store'), [
                'supplier_id' => $supplierId,
                'purchase_date' => '2026-11-01',
                'payment_status' => 'pending',
                'voucher_section' => 'PURCHASE',
                'less_kasar' => '0.500',
                'rate_cut_weight' => '0.100',
                'other_convert_fine' => '1.000',
                'opening_gold' => '2.000',
                'closing_gold' => '3.000',
                'footer_note' => 'Supplier bill note',
                'items' => [[
                    'product_id' => $productId,
                    'pcs' => 2,
                    'gross_weight' => '22.000',
                    'net_weight' => '20.000',
                    'touch' => '95.000',
                    'wastage' => '2.500',
                ]],
            ])
            ->assertRedirect(route('supplier-bills.index'));

        $billId = DB::table('purchasemaster')->value('purchase_id');
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $billId,
            'product_id' => $productId,
            'quantity' => 2,
            'pcs' => 2,
            'fine_weight' => 19.500,
            'unit_price' => 0,
        ]);
        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 2,
        ]);

        $this->withCookie('GTA', (string) $userId)
            ->get(route('supplier-bills.show', $billId))
            ->assertOk()
            ->assertSee('19.500')
            ->assertSee('Supplier bill note');

        $this->withCookie('GTA', (string) $userId)
            ->get(route('supplier-bills.edit', $billId))
            ->assertOk()
            ->assertSee('Supplier Gold Balance');

        $this->withCookie('GTA', (string) $userId)
            ->put(route('supplier-bills.update', $billId), [
                'supplier_id' => $supplierId,
                'purchase_date' => '2026-11-02',
                'payment_status' => 'paid',
                'voucher_section' => 'PURCHASE',
                'items' => [[
                    'product_id' => $productId,
                    'pcs' => 3,
                    'gross_weight' => '12.000',
                    'net_weight' => '10.000',
                    'touch' => '95.000',
                    'wastage' => '2.500',
                ]],
            ])
            ->assertRedirect(route('supplier-bills.show', $billId));

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $billId,
            'quantity' => 3,
            'pcs' => 3,
            'fine_weight' => 9.750,
        ]);
        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 3,
        ]);

        $this->withCookie('GTA', (string) $userId)
            ->get(route('supplier-bills.pdf', $billId))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_standard_supplier_bill_still_calculates_currency_totals(): void
    {
        [$userId, $supplierId, $productId] = $this->createWorkspace(false);

        $this->withCookie('GTA', (string) $userId)
            ->post(route('supplier-bills.store'), [
                'supplier_id' => $supplierId,
                'purchase_date' => '2026-11-01',
                'payment_status' => 'pending',
                'items' => [[
                    'product_id' => $productId,
                    'quantity' => 2,
                    'unit_price' => '10.00',
                    'tax_percent' => '10',
                    'discount_amount' => '0.00',
                ]],
            ])
            ->assertRedirect(route('supplier-bills.index'));

        $this->assertDatabaseHas('purchasemaster', [
            'subtotal' => 20,
            'total_tax' => 2,
            'grand_total' => 22,
        ]);
        $this->assertDatabaseHas('inventorymaster', [
            'product_id' => $productId,
            'quantity_in_stock' => 2,
        ]);
    }

    private function createWorkspace(bool $isJewelry): array
    {
        $now = now();
        $userId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Test Workspace',
            'email' => uniqid('workspace').'@example.com',
            'password' => 'unused',
            'is_admin' => 'N',
            'status' => '1',
            'is_jewelry' => $isJewelry,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $supplierId = DB::table('suppliermaster')->insertGetId([
            'supplier_code' => uniqid('supp'),
            'supplier_name' => 'Test Supplier',
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
            'product_code' => uniqid('product'),
            'product_name' => 'Test Jewellery',
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'purchase_price' => 10,
            'selling_price' => 12,
            'status' => '1',
            'created_by' => $userId,
            'touch' => 95,
            'default_wastage' => 2.5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$userId, $supplierId, $productId];
    }
}
