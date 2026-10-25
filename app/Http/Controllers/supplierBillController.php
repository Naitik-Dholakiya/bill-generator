<?php

namespace App\Http\Controllers;

use App\Models\InventoryMaster;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class supplierBillController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $bills = DB::table('purchasemaster as bills')
            ->join('suppliermaster as suppliers', 'suppliers.supplier_id', '=', 'bills.supplier_id')
            ->where('bills.created_by', $userId)
            ->whereNull('bills.deleted_at')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('bills.purchase_number', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.supplier_name', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('payment_status'), fn ($query) => $query->where('bills.payment_status', $request->input('payment_status')))
            ->orderByDesc('bills.purchase_id')
            ->select('bills.*', 'suppliers.supplier_name')
            ->paginate(15)
            ->withQueryString();

        return view('supplier-bills.index', compact('bills'));
    }

    public function create(Request $request)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $suppliers = $this->activeSuppliers($userId);
        $products = $this->activeProducts($userId);
        $billNumber = $this->generateBillNumber($userId);
        $rows = collect(old('items', [['product_id' => '', 'quantity' => 1, 'unit_price' => '', 'tax_percent' => 0, 'discount_amount' => 0]]))
            ->map(fn ($item) => (object) (is_array($item) ? $item : []))
            ->values();
        if ($rows->isEmpty()) {
            $rows = collect([(object) []]);
        }

        return view('supplier-bills.create', compact('suppliers', 'products', 'billNumber', 'rows'));
    }

    public function store(Request $request)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $validated = $this->validateBill($request, $userId);

        DB::transaction(function () use ($validated, $userId) {
            $totals = $this->calculateTotals($validated['items']);
            $now = now();
            $billId = DB::table('purchasemaster')->insertGetId([
                'purchase_number' => $this->generateBillNumber($userId),
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                ...$totals,
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_by' => $userId,
                'updated_at' => $now,
            ]);

            $this->replaceItems($billId, $validated['items'], $userId, $now);
            $this->adjustStock($this->quantitiesByProduct($validated['items']), $userId);
        });

        return redirect()
            ->route('supplier-bills.index')
            ->with('success', 'Supplier bill created successfully.');
    }

    public function show(Request $request, int $billId)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $bill = $this->findBill($billId, $userId);
        $items = $this->billItems($billId);

        return view('supplier-bills.view', compact('bill', 'items'));
    }

    public function pdf(Request $request, int $billId)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $bill = $this->findBill($billId, $userId);
        $items = $this->billItems($billId);
        $supplier = DB::table('suppliermaster')
            ->where('supplier_id', $bill->supplier_id)
            ->first(['supplier_address', 'phone', 'email', 'gst_number']);
        $workspace = DB::table('usermaster')
            ->where('user_id', $userId)
            ->first(['full_name', 'company_name', 'company_address', 'company_gstin', 'phone']);
        $pdf = Pdf::loadView('supplier-bills.pdf', compact('bill', 'items', 'supplier', 'workspace'))
            ->setPaper('a4', 'portrait');
        $filename = 'Supplier-Bill-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $bill->purchase_number).'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    public function edit(Request $request, int $billId)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $bill = $this->findBill($billId, $userId);
        $suppliers = $this->activeSuppliers($userId, $bill->supplier_id);
        $items = $this->billItems($billId);
        $productIds = $items->pluck('product_id')->all();
        $products = $this->activeProducts($userId, $productIds);
        $rows = collect(old('items', $items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'tax_percent' => $item->tax_percent,
            'discount_amount' => $item->discount_amount,
        ])->all()))->map(fn ($item) => (object) (is_array($item) ? $item : []))->values();
        if ($rows->isEmpty()) {
            $rows = collect([(object) []]);
        }

        return view('supplier-bills.edit', compact('bill', 'suppliers', 'products', 'rows'));
    }

    public function update(Request $request, int $billId)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $bill = $this->findBill($billId, $userId);
        $oldItems = $this->billItems($billId);
        $validated = $this->validateBill(
            $request,
            $userId,
            (int) $bill->supplier_id,
            $oldItems->pluck('product_id')->map(fn ($productId) => (int) $productId)->all()
        );

        DB::transaction(function () use ($validated, $billId, $userId, $oldItems) {
            $oldQuantities = $this->quantitiesByProduct($oldItems->all());
            $newQuantities = $this->quantitiesByProduct($validated['items']);
            $stockDeltas = [];

            foreach (array_unique(array_merge(array_keys($oldQuantities), array_keys($newQuantities))) as $productId) {
                $stockDeltas[$productId] = ($newQuantities[$productId] ?? 0) - ($oldQuantities[$productId] ?? 0);
            }

            $totals = $this->calculateTotals($validated['items']);
            $now = now();
            DB::table('purchasemaster')
                ->where('purchase_id', $billId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->update([
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_date' => $validated['purchase_date'],
                    ...$totals,
                    'payment_status' => $validated['payment_status'],
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

            DB::table('purchase_items')
                ->where('purchase_id', $billId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);

            $this->replaceItems($billId, $validated['items'], $userId, $now);
            $this->adjustStock($stockDeltas, $userId);
        });

        return redirect()
            ->route('supplier-bills.show', $billId)
            ->with('success', 'Supplier bill updated successfully.');
    }

    public function destroy(Request $request, int $billId)
    {
        $userId = $request->attributes->get('authenticated_user_id');
        $this->findBill($billId, $userId);

        DB::transaction(function () use ($billId, $userId) {
            $items = $this->billItems($billId);
            $quantities = $this->quantitiesByProduct($items->all());
            $reversal = array_map(fn ($quantity) => -$quantity, $quantities);
            $this->adjustStock($reversal, $userId);

            $now = now();
            DB::table('purchasemaster')
                ->where('purchase_id', $billId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);
            DB::table('purchase_items')
                ->where('purchase_id', $billId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);
        });

        return redirect()
            ->route('supplier-bills.index')
            ->with('success', 'Supplier bill deleted and its stock quantities reversed.');
    }

    private function validateBill(
        Request $request,
        int $userId,
        ?int $currentSupplierId = null,
        array $currentProductIds = []
    ): array
    {
        return $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliermaster', 'supplier_id')->where(function ($query) use ($userId, $currentSupplierId) {
                    $query->where('created_by', $userId)
                        ->where(function ($query) use ($currentSupplierId) {
                            $query->where(function ($active) {
                                $active->where('status', '1')->whereNull('deleted_at');
                            });
                            if ($currentSupplierId !== null) {
                                $query->orWhere('supplier_id', $currentSupplierId);
                            }
                        });
                }),
            ],
            'purchase_date' => ['required', 'date'],
            'payment_status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('productmaster', 'product_id')->where(function ($query) use ($userId, $currentProductIds) {
                    $query->where('created_by', $userId)
                        ->where(function ($query) use ($currentProductIds) {
                            $query->where(function ($active) {
                                $active->where('status', '1')->whereNull('deleted_at');
                            });
                            if ($currentProductIds !== []) {
                                $query->orWhereIn('product_id', $currentProductIds);
                            }
                        });
                }),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'items.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ]);
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;
        $grandTotal = 0;

        foreach ($items as $item) {
            $lineSubtotal = (int) $item['quantity'] * (float) $item['unit_price'];
            $taxAmount = round($lineSubtotal * (float) ($item['tax_percent'] ?? 0) / 100, 2);
            $discountAmount = (float) ($item['discount_amount'] ?? 0);

            $subtotal += $lineSubtotal;
            $totalTax += $taxAmount;
            $totalDiscount += $discountAmount;
            $grandTotal += max($lineSubtotal + $taxAmount - $discountAmount, 0);
        }

        if (max($subtotal, $totalTax, $totalDiscount, $grandTotal) > 9999999999.99) {
            throw ValidationException::withMessages([
                'items' => 'The bill total exceeds the supported amount.',
            ]);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total_tax' => round($totalTax, 2),
            'discount_amount' => round($totalDiscount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    private function replaceItems(int $billId, array $items, int $userId, $now): void
    {
        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $taxPercent = (float) ($item['tax_percent'] ?? 0);
            $discountAmount = (float) ($item['discount_amount'] ?? 0);
            $subtotal = $quantity * $unitPrice;
            $taxAmount = round($subtotal * $taxPercent / 100, 2);

            DB::table('purchase_items')->insert([
                'purchase_id' => $billId,
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'line_total' => round(max($subtotal + $taxAmount - $discountAmount, 0), 2),
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function quantitiesByProduct(array $items): array
    {
        $quantities = [];
        foreach ($items as $item) {
            $productId = (int) (is_array($item) ? $item['product_id'] : $item->product_id);
            $quantity = (int) (is_array($item) ? $item['quantity'] : $item->quantity);
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
        }

        return $quantities;
    }

    private function adjustStock(array $deltas, int $userId): void
    {
        foreach ($deltas as $productId => $delta) {
            if ($delta === 0) {
                continue;
            }

            $inventory = InventoryMaster::withTrashed()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();
            $currentQuantity = $inventory && !$inventory->trashed()
                ? (int) $inventory->quantity_in_stock
                : 0;
            $newQuantity = $currentQuantity + $delta;

            if ($newQuantity < 0 || $newQuantity > 2147483647) {
                throw ValidationException::withMessages([
                    'stock' => 'This change would put the stock quantity outside its supported range. Update the stock quantity before editing or deleting this bill.',
                ]);
            }

            if (!$inventory) {
                InventoryMaster::create([
                    'product_id' => $productId,
                    'quantity_in_stock' => $newQuantity,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                continue;
            }

            if ($inventory->trashed()) {
                $inventory->restore();
            }

            $inventory->quantity_in_stock = $newQuantity;
            $inventory->updated_by = $userId;
            $inventory->save();
        }
    }

    private function findBill(int $billId, int $userId): object
    {
        $bill = DB::table('purchasemaster as bills')
            ->join('suppliermaster as suppliers', 'suppliers.supplier_id', '=', 'bills.supplier_id')
            ->where('bills.purchase_id', $billId)
            ->where('bills.created_by', $userId)
            ->whereNull('bills.deleted_at')
            ->select('bills.*', 'suppliers.supplier_name')
            ->first();

        abort_if(!$bill, 404, 'Supplier bill not found.');

        return $bill;
    }

    private function billItems(int $billId)
    {
        return DB::table('purchase_items as items')
            ->join('productmaster as products', 'products.product_id', '=', 'items.product_id')
            ->where('items.purchase_id', $billId)
            ->whereNull('items.deleted_at')
            ->orderBy('items.purchase_item_id')
            ->select('items.*', 'products.product_name', 'products.product_code')
            ->get();
    }

    private function activeSuppliers(int $userId, ?int $includeSupplierId = null)
    {
        return DB::table('suppliermaster')
            ->where('created_by', $userId)
            ->where(function ($query) use ($includeSupplierId) {
                $query->where(function ($active) {
                    $active->where('status', '1')->whereNull('deleted_at');
                });
                if ($includeSupplierId !== null) {
                    $query->orWhere('supplier_id', $includeSupplierId);
                }
            })
            ->orderBy('supplier_name')
            ->get();
    }

    private function activeProducts(int $userId, array $includeProductIds = [])
    {
        return DB::table('productmaster')
            ->where('created_by', $userId)
            ->where(function ($query) use ($includeProductIds) {
                $query->where(function ($active) {
                    $active->where('status', '1')->whereNull('deleted_at');
                });
                if ($includeProductIds !== []) {
                    $query->orWhereIn('product_id', $includeProductIds);
                }
            })
            ->orderBy('product_name')
            ->get(['product_id', 'product_code', 'product_name', 'purchase_price']);
    }

    private function generateBillNumber(int $userId): string
    {
        $prefix = 'BILL'.date('Y').$userId;
        $lastNumber = DB::table('purchasemaster')
            ->where('created_by', $userId)
            ->where('purchase_number', 'like', $prefix.'%')
            ->orderByDesc('purchase_id')
            ->value('purchase_number');
        $sequence = $lastNumber
            ? (int) substr($lastNumber, strlen($prefix)) + 1
            : 1;

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
