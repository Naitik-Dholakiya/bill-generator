<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Services\DocumentNumberGenerator;
use App\Services\InventoryLedger;
use Illuminate\Validation\ValidationException;

class invoiceController extends Controller
{
    /**
     * Display invoice list.
     */
    public function index()
    {
        $userId = Cookie::get('GTA');
        $isJewelryUser = $this->isJewelryUser($userId);
        $invoices = DB::table('invoicemaster')
            ->leftJoin(
                'customermaster',
                'invoicemaster.customer_id',
                '=',
                'customermaster.customer_id'
            )
            ->leftJoinSub(
                DB::table('invoice_items')
                    ->select('invoice_id', DB::raw('SUM(fine_weight) as total_fine'))
                    ->whereNull('deleted_at')
                    ->groupBy('invoice_id'),
                'jewellery_totals',
                'jewellery_totals.invoice_id',
                '=',
                'invoicemaster.invoice_id'
            )
            ->select(
                'invoicemaster.*',
                DB::raw('COALESCE(invoicemaster.customer_name_snapshot, customermaster.customer_name) as customer_name'),
                'jewellery_totals.total_fine'
            )
            ->whereNull('invoicemaster.deleted_at')
            ->where('invoicemaster.created_by', $userId)
            ->orderByDesc('invoicemaster.invoice_id')
            ->paginate(15);

        return view($isJewelryUser ? 'invoice.jewellery.index' : 'invoice.index', compact('invoices'));
    }

    public function create()
    {
        $userId = Cookie::get('GTA');

        //Get Active Customer
        $customers = DB::table('customermaster')
            ->where('created_by', $userId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderBy('customer_name')
            ->get();

        // Get Active Products
        $products = DB::table('productmaster')
            ->where('created_by', $userId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderBy('product_name')
            ->get();


        // Generate Invoice Number
        $invoiceNumber = app(DocumentNumberGenerator::class)->previewInvoiceNumber((int) $userId);
        $oldItems = old('items', []);
        $formItems = collect(is_array($oldItems)
            ? array_map(fn ($item) => (object) (is_array($item) ? $item : []), $oldItems)
            : []);
        $isJewelryUser = $this->isJewelryUser($userId);

        $view = $isJewelryUser ? 'invoice.jewellery.add' : 'invoice.create';
        $rows = $formItems->isEmpty() ? collect([(object) []]) : $formItems;

        return view($view, compact('customers', 'products', 'invoiceNumber', 'formItems', 'rows', 'isJewelryUser'));
    }

    public function edit(int $invoiceId)
    {
        $userId = Cookie::get('GTA');
        $invoice = DB::table('invoicemaster')
            ->where('invoice_id', $invoiceId)
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');
        $items = DB::table('invoice_items as it')
            ->leftJoin('productmaster as p', 'p.product_id', '=', 'it.product_id')
            ->where('it.invoice_id', $invoiceId)
            ->whereNull('it.deleted_at')
            ->orderBy('it.item_id')
            ->select('it.*', 'p.touch as product_touch', 'p.default_wastage as product_default_wastage')
            ->get();
        $isJewelryUser = $this->isJewelryUser($userId);

        $customers = DB::table('customermaster')
            ->where('created_by', $userId)
            ->where(function ($query) use ($invoice) {
                $query->where(function ($activeCustomers) {
                    $activeCustomers->where('status', '1')
                        ->whereNull('deleted_at');
                })
                    ->orWhere('customer_id', $invoice->customer_id);
            })
            ->orderBy('customer_name')
            ->get();

        $productIds = $items->pluck('product_id')->all();
        $products = DB::table('productmaster')
            ->where(function ($query) use ($productIds) {
                $query->where(function ($active) {
                    $active->where('status', '1')
                        ->whereNull('deleted_at');
                })
                    ->orWhereIn('product_id', $productIds);
            })
            ->where('created_by', $userId)
            ->orderBy('product_name')
            ->get();

        $oldItems = old('items');
        if (is_array($oldItems)) {
            $rows = collect($oldItems)
                ->map(fn ($item) => (object) (is_array($item) ? $item : []));
        } else {
            $rows = $items;
        }

        if ($rows->isEmpty()) {
            $rows = collect([(object) []]);
        }

        $view = $isJewelryUser ? 'invoice.jewellery.edit' : 'invoice.edit';

        return view($view, compact('invoice', 'items', 'rows', 'customers', 'products', 'isJewelryUser'));
    }

    public function view(int $invoiceId)
    {
        $userId = Cookie::get('GTA');
        $invoice = DB::table('invoicemaster as i')
            ->leftJoin('customermaster as c', 'c.customer_id', '=', 'i.customer_id')
            ->where('i.invoice_id', $invoiceId)
            ->where('i.created_by', $userId)
            ->whereNull('i.deleted_at')
            ->select(
                'i.*',
                DB::raw('COALESCE(i.customer_name_snapshot, c.customer_name) as customer_name'),
                DB::raw('COALESCE(i.customer_phone_snapshot, c.phone) as phone'),
                DB::raw('COALESCE(i.customer_email_snapshot, c.email) as email'),
                DB::raw('COALESCE(i.customer_address_snapshot, c.billing_address) as billing_address'),
                DB::raw('COALESCE(i.customer_gstin_snapshot, c.gst_number) as gst_number')
            )
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');
        $isJewelryUser = $this->isJewelryUser($userId);

        $items = DB::table('invoice_items as it')
            ->leftJoin('productmaster as p', 'p.product_id', '=', 'it.product_id')
            ->where('it.invoice_id', $invoiceId)
            ->whereNull('it.deleted_at')
            ->orderBy('it.item_id')
            ->select(
                'it.*',
                DB::raw('COALESCE(it.product_name_snapshot, p.product_name) as product_name'),
                DB::raw('COALESCE(it.product_code_snapshot, p.product_code) as product_code')
            )
            ->get();
        $payments = $isJewelryUser ? collect() : DB::table('invoice_payments')
            ->where('invoice_id', $invoiceId)
            ->orderByDesc('paid_at')
            ->orderByDesc('invoice_payment_id')
            ->get();
        $revisions = DB::table('invoice_revisions')
            ->where('invoice_id', $invoiceId)
            ->orderByDesc('revision_number')
            ->get()
            ->map(function ($revision) {
                $revision->snapshot = json_decode($revision->snapshot, true, 512, JSON_THROW_ON_ERROR);

                return $revision;
            });

        $view = $isJewelryUser ? 'invoice.jewellery.view' : 'invoice.view';
        $rows = $items;

        return view($view, compact('invoice', 'items', 'rows', 'payments', 'revisions', 'isJewelryUser'));
    }

    public function delete(int $invoiceId)
    {
        $userId = Cookie::get('GTA');
        $invoice = DB::table('invoicemaster')
            ->where('invoice_id', $invoiceId)
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');

        DB::transaction(function () use ($invoiceId, $userId) {
            $invoice = DB::table('invoicemaster')
                ->where('invoice_id', $invoiceId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            abort_if(! $invoice, 404, 'Invoice not found.');

            $items = DB::table('invoice_items')
                ->where('invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->get()
                ->all();
            $this->recordInvoiceRevision($invoice, $items, (int) $userId);

            if ($invoice->stock_tracked) {
                foreach ($this->quantitiesByItems($items) as $productId => $quantity) {
                    app(InventoryLedger::class)->applyMovement(
                        $productId,
                        $quantity,
                        (int) $userId,
                        'sale_reversal',
                        'invoice',
                        $invoiceId,
                        'Invoice deleted.',
                        true
                    );
                }
            }

            $now = now();

            DB::table('invoicemaster')
                ->where('invoice_id', $invoiceId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);

            DB::table('invoice_items')
                ->where('invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);
        });

        return redirect()
            ->route('invoice.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function update(Request $request, int $invoiceId)
    {
        $userId = Cookie::get('GTA');
        $invoice = DB::table('invoicemaster')
            ->where('invoice_id', $invoiceId)
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');
        $isJewelryUser = $this->isJewelryUser($userId);
        if ($isJewelryUser) {
            return $this->saveJewelryInvoice($request, $userId, $invoiceId);
        }

        $rules = [
            'customer_id' => ['required', 'integer', Rule::exists('customermaster', 'customer_id')->where('created_by', $userId)],
            'invoice_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('productmaster', 'product_id')->where(function ($query) use ($userId) {
                    $query->where('created_by', $userId)->whereNull('deleted_at');
                }),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
        if ($isJewelryUser) {
            $rules['items.*.touch'] = ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'];
        }
        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $invoiceId, $userId, $isJewelryUser) {
            $invoice = DB::table('invoicemaster')
                ->where('invoice_id', $invoiceId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            abort_if(! $invoice, 404, 'Invoice not found.');

            $oldItems = DB::table('invoice_items')
                ->where('invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->get()
                ->all();
            $this->recordInvoiceRevision($invoice, $oldItems, (int) $userId);
            $headerSnapshot = $this->invoiceSnapshot((int) $userId, (int) $validated['customer_id']);
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $grandTotal = 0;
            $now = now();

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $taxAmount = (float) ($item['tax_amount'] ?? 0);
                $discountAmount = (float) ($item['discount_amount'] ?? 0);
                $itemSubtotal = $quantity * $unitPrice;

                $subtotal += $itemSubtotal;
                $totalTax += $taxAmount;
                $totalDiscount += $discountAmount;
                $grandTotal += max($itemSubtotal + $taxAmount - $discountAmount, 0);
            }

            $grandTotal = round($grandTotal, 2);
            $openingPaidAmount = $invoice->opening_paid_amount === null ? null : (float) $invoice->opening_paid_amount;
            $recordedPayments = (float) DB::table('invoice_payments')
                ->where('invoice_id', $invoiceId)
                ->whereNull('voided_at')
                ->sum('amount');
            if ($openingPaidAmount !== null
                && $this->moneyCents($openingPaidAmount) + $this->moneyCents($recordedPayments) > $this->moneyCents($grandTotal)) {
                throw ValidationException::withMessages([
                    'items' => 'The invoice total cannot be lower than the amount already paid.',
                ]);
            }

            $newQuantities = $this->quantitiesByItems($validated['items']);
            if ($invoice->stock_tracked) {
                $oldQuantities = $this->quantitiesByItems($oldItems);
                foreach (array_unique(array_merge(array_keys($oldQuantities), array_keys($newQuantities))) as $productId) {
                    $delta = ($oldQuantities[$productId] ?? 0) - ($newQuantities[$productId] ?? 0);
                    app(InventoryLedger::class)->applyMovement(
                        (int) $productId,
                        (int) $delta,
                        (int) $userId,
                        'sale_adjustment',
                        'invoice',
                        $invoiceId,
                        'Invoice items updated.',
                        true
                    );
                }
            }

            $paidAmount = ($openingPaidAmount ?? 0) + $recordedPayments;
            $paymentStatus = $grandTotal > 0 && ($openingPaidAmount !== null || $recordedPayments > 0)
                ? $this->paymentStatus($paidAmount, $grandTotal)
                : $invoice->payment_status;

            DB::table('invoicemaster')
                ->where('invoice_id', $invoiceId)
                ->where('created_by', $userId)
                ->whereNull('deleted_at')
                ->update([
                    'customer_id' => $validated['customer_id'],
                    'invoice_date' => $validated['invoice_date'],
                    'subtotal' => round($subtotal, 2),
                    'total_tax' => round($totalTax, 2),
                    'discount_amount' => round($totalDiscount, 2),
                    'grand_total' => $grandTotal,
                    'payment_status' => $paymentStatus,
                    ...$headerSnapshot,
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

            DB::table('invoice_items')
                ->where('invoice_id', $invoiceId)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $taxAmount = (float) ($item['tax_amount'] ?? 0);
                $discountAmount = (float) ($item['discount_amount'] ?? 0);
                $itemSubtotal = $quantity * $unitPrice;

                $product = $this->productSnapshot((int) $userId, (int) $item['product_id']);
                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'product_name_snapshot' => $product->product_name,
                    'product_code_snapshot' => $product->product_code,
                    'touch' => $isJewelryUser ? ($item['touch'] ?? null) : null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'tax_percent' => $itemSubtotal > 0 ? round(($taxAmount / $itemSubtotal) * 100, 2) : 0,
                    'tax_amount' => $taxAmount,
                    'line_total' => round(max($itemSubtotal + $taxAmount - $discountAmount, 0), 2),
                    'created_at' => $now,
                    'created_by' => $userId,
                ]);
            }
        });

        return redirect()
            ->route('invoice.index')
            ->with('success', 'Invoice updated successfully.');
    }

    public function createInvoicePost(Request $request)
    {
        $userId = Cookie::get('GTA');
        $isJewelryUser = $this->isJewelryUser($userId);
        if ($isJewelryUser) {
            return $this->saveJewelryInvoice($request, $userId);
        }

        $rules = [
            'invoice_number' => [
                'required',
                'string',
                'max:50'
            ],

            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customermaster', 'customer_id')->where('created_by', $userId)
            ],

            'invoice_date' => [
                'required',
                'date'
            ],

            'subtotal' => [
                'required',
                'numeric',
                'min:0'
            ],

            'total_tax' => [
                'required',
                'numeric',
                'min:0'
            ],

            'discount_amount' => [
                'required',
                'numeric',
                'min:0'
            ],

            'grand_total' => [
                'required',
                'numeric',
                'min:0'
            ],

            'notes' => [
                'nullable',
                'string'
            ],

            'items' => [
                'required',
                'array',
                'min:1'
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('productmaster', 'product_id')->where(function ($query) use ($userId) {
                    $query->where('created_by', $userId)
                        ->where('status', '1')
                        ->whereNull('deleted_at');
                }),
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'items.*.tax_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'items.*.discount_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],

        ];
        if ($isJewelryUser) {
            $rules['items.*.touch'] = ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'];
        }
        $validated = $request->validate($rules);

        // Start Database Transaction
        DB::beginTransaction();
        try {
            $invoiceNumber = app(DocumentNumberGenerator::class)->nextInvoiceNumber((int) $userId);
            $headerSnapshot = $this->invoiceSnapshot((int) $userId, (int) $validated['customer_id']);

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $grandTotal = 0;


            foreach ($validated['items'] as $item) {

                $quantity = (int) $item['quantity'];

                $unitPrice = (float) $item['unit_price'];

                $taxAmount = isset($item['tax_amount'])
                    ? (float) $item['tax_amount']
                    : 0;

                $discountAmount = isset($item['discount_amount'])
                    ? (float) $item['discount_amount']
                    : 0;

                $itemSubtotal = $quantity * $unitPrice;

                $itemTotal = $itemSubtotal + $taxAmount- $discountAmount;

                $itemTotal = max($itemTotal, 0);

                $subtotal += $itemSubtotal;

                $totalTax += $taxAmount;

                $totalDiscount += $discountAmount;

                $grandTotal += $itemTotal;
            }

            $subtotal = round($subtotal, 2);

            $totalTax = round($totalTax, 2);

            $totalDiscount = round($totalDiscount, 2);

            $grandTotal = round($grandTotal, 2);

            $invoiceId = DB::table('invoicemaster')->insertGetId([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'discount_amount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'payment_status' => 'pending',
                'opening_paid_amount' => 0,
                'stock_tracked' => true,
                ...$headerSnapshot,
                'notes' => $validated['notes'] ?? null,
                'created_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];

                $unitPrice = (float) $item['unit_price'];

                $taxAmount = isset($item['tax_amount'])
                    ? (float) $item['tax_amount']
                    : 0;

                $discountAmount = isset($item['discount_amount'])
                    ? (float) $item['discount_amount']
                    : 0;


                $itemSubtotal =
                    $quantity * $unitPrice;


                $itemTotal =
                    $itemSubtotal
                    + $taxAmount
                    - $discountAmount;


                $itemTotal =
                    max($itemTotal, 0);

                $tax_percentage = $itemSubtotal > 0
                    ? ($taxAmount / $itemSubtotal) * 100
                    : 0;

                $product = $this->productSnapshot((int) $userId, (int) $item['product_id']);
                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'product_name_snapshot' => $product->product_name,
                    'product_code_snapshot' => $product->product_code,
                    'touch' => $isJewelryUser ? ($item['touch'] ?? null) : null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'tax_percent' => round($tax_percentage, 2),
                    'tax_amount' => $taxAmount,
                    'line_total' => round(
                        $itemTotal,
                        2
                    ),
                    'created_at' => now(),
                    'created_by' => $userId,
                ]);

                app(InventoryLedger::class)->applyMovement(
                    (int) $item['product_id'],
                    -$quantity,
                    (int) $userId,
                    'sale',
                    'invoice',
                    $invoiceId,
                    'Invoice sale.',
                    true
                );
            }

            DB::commit();

            return redirect()
                ->route('invoice.index')
                ->with(
                    'success',
                    'Invoice created successfully.'
                );
            // return $this->pdf($invoiceId);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with(
                    'error',
                    'Unable to create invoice. ' .
                    $e->getMessage()
                );
        }
    }

    private function invoiceSnapshot(int $userId, int $customerId): array
    {
        $customer = DB::table('customermaster')
            ->where('customer_id', $customerId)
            ->where('created_by', $userId)
            ->first(['customer_name', 'gst_number', 'billing_address', 'phone', 'email']);
        abort_if(! $customer, 404, 'Customer not found.');

        $seller = DB::table('usermaster')
            ->where('user_id', $userId)
            ->first(['full_name', 'company_name', 'company_gstin', 'company_address', 'phone']);
        abort_if(! $seller, 404, 'Workspace not found.');

        return [
            'customer_name_snapshot' => $customer->customer_name,
            'customer_gstin_snapshot' => $customer->gst_number,
            'customer_address_snapshot' => $customer->billing_address,
            'customer_phone_snapshot' => $customer->phone,
            'customer_email_snapshot' => $customer->email,
            'seller_name_snapshot' => $seller->company_name ?: $seller->full_name,
            'seller_gstin_snapshot' => $seller->company_gstin,
            'seller_address_snapshot' => $seller->company_address,
            'seller_phone_snapshot' => $seller->phone,
        ];
    }

    private function productSnapshot(int $userId, int $productId): object
    {
        $product = DB::table('productmaster')
            ->where('product_id', $productId)
            ->where('created_by', $userId)
            ->first(['product_name', 'product_code']);
        abort_if(! $product, 404, 'Product not found.');

        return $product;
    }

    private function quantitiesByItems(array $items): array
    {
        $quantities = [];
        foreach ($items as $item) {
            $productId = (int) (is_array($item) ? $item['product_id'] : $item->product_id);
            $quantity = (int) (is_array($item) ? ($item['quantity'] ?? $item['pcs'] ?? 0) : ($item->quantity ?? $item->pcs ?? 0));
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
        }

        return $quantities;
    }

    private function paymentStatus(float $paidAmount, float $grandTotal): string
    {
        if ($paidAmount >= $grandTotal) {
            return 'paid';
        }

        return $paidAmount > 0 ? 'partial' : 'pending';
    }

    private function moneyCents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function recordInvoiceRevision(object $invoice, array $items, int $userId): void
    {
        $revisionNumber = (int) DB::table('invoice_revisions')
            ->where('invoice_id', $invoice->invoice_id)
            ->max('revision_number') + 1;

        DB::table('invoice_revisions')->insert([
            'invoice_id' => $invoice->invoice_id,
            'revision_number' => $revisionNumber,
            'snapshot' => json_encode([
                'invoice' => (array) $invoice,
                'items' => array_map(fn ($item) => (array) $item, $items),
            ], JSON_THROW_ON_ERROR),
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }

    private function saveJewelryInvoice(Request $request, $userId, ?int $invoiceId = null)
    {
        $isUpdate = $invoiceId !== null;
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50'],
            'customer_id' => ['required', 'integer', Rule::exists('customermaster', 'customer_id')->where('created_by', $userId)],
            'invoice_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'voucher_section' => ['required', 'string', 'max:50'],
            'less_kasar' => ['nullable', 'numeric', 'min:0'],
            'rate_cut_weight' => ['nullable', 'numeric', 'min:0'],
            'other_convert_fine' => ['nullable', 'numeric', 'min:0'],
            'opening_gold' => ['nullable', 'numeric', 'min:0'],
            'closing_gold' => ['nullable', 'numeric', 'min:0'],
            'footer_note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('productmaster', 'product_id')->where(function ($query) use ($userId, $invoiceId) {
                    $query->where('created_by', $userId)
                        ->where(function ($query) use ($invoiceId) {
                            $query->where(function ($active) {
                                $active->where('status', '1')->whereNull('deleted_at');
                            });
                            if ($invoiceId !== null) {
                                $existingProductIds = DB::table('invoice_items')
                                    ->where('invoice_id', $invoiceId)
                                    ->whereNull('deleted_at')
                                    ->pluck('product_id');
                                $query->orWhereIn('product_id', $existingProductIds);
                            }
                        });
                }),
            ],
            'items.*.pcs' => ['required', 'integer', 'min:1'],
            'items.*.gross_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.net_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.touch' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'],
            'items.*.wastage' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'],
        ]);

        $now = now();

        DB::transaction(function () use ($validated, $userId, $invoiceId, $now) {
            $existingInvoice = null;
            $oldItems = [];
            if ($invoiceId !== null) {
                $existingInvoice = DB::table('invoicemaster')
                    ->where('invoice_id', $invoiceId)
                    ->where('created_by', $userId)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();
                abort_if(! $existingInvoice, 404, 'Invoice not found.');
                $oldItems = DB::table('invoice_items')
                    ->where('invoice_id', $invoiceId)
                    ->whereNull('deleted_at')
                    ->get()
                    ->all();
                $this->recordInvoiceRevision($existingInvoice, $oldItems, (int) $userId);
            }

            $headerSnapshot = $this->invoiceSnapshot((int) $userId, (int) $validated['customer_id']);
            $invoiceData = [
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                // The shared invoice schema requires currency totals; jewellery vouchers are weight-only.
                'subtotal' => 0,
                'total_tax' => 0,
                'discount_amount' => 0,
                'grand_total' => 0,
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'voucher_section' => $validated['voucher_section'],
                'less_kasar' => $validated['less_kasar'] ?? null,
                'rate_cut_weight' => $validated['rate_cut_weight'] ?? null,
                'rate' => null,
                'rate_cut_amount' => null,
                'other_convert_fine' => $validated['other_convert_fine'] ?? null,
                'opening_balance' => null,
                'opening_gold' => $validated['opening_gold'] ?? null,
                'closing_balance' => null,
                'closing_gold' => $validated['closing_gold'] ?? null,
                'closing_gold_type' => 'Dr',
                'footer_note' => $validated['footer_note'] ?? null,
                ...$headerSnapshot,
                'updated_by' => $userId,
                'updated_at' => $now,
            ];

            if ($invoiceId === null) {
                $invoiceData['invoice_number'] = app(DocumentNumberGenerator::class)->nextInvoiceNumber((int) $userId);
                $invoiceData['created_at'] = $now;
                $invoiceData['created_by'] = $userId;
                $invoiceData['opening_paid_amount'] = 0;
                $invoiceData['stock_tracked'] = true;
                $invoiceId = DB::table('invoicemaster')->insertGetId($invoiceData);
            } else {
                $invoiceData['payment_status'] = $existingInvoice->payment_status;
                DB::table('invoicemaster')
                    ->where('invoice_id', $invoiceId)
                    ->where('created_by', $userId)
                    ->whereNull('deleted_at')
                    ->update($invoiceData);

                DB::table('invoice_items')
                    ->where('invoice_id', $invoiceId)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);
            }

            $newQuantities = $this->quantitiesByItems($validated['items']);
            if ($existingInvoice?->stock_tracked) {
                $oldQuantities = $this->quantitiesByItems($oldItems);
                foreach (array_unique(array_merge(array_keys($oldQuantities), array_keys($newQuantities))) as $productId) {
                    app(InventoryLedger::class)->applyMovement(
                        (int) $productId,
                        ($oldQuantities[$productId] ?? 0) - ($newQuantities[$productId] ?? 0),
                        (int) $userId,
                        'sale_adjustment',
                        'invoice',
                        $invoiceId,
                        'Jewellery voucher items updated.',
                        true
                    );
                }
            } elseif ($existingInvoice === null) {
                foreach ($newQuantities as $productId => $quantity) {
                    app(InventoryLedger::class)->applyMovement(
                        (int) $productId,
                        -$quantity,
                        (int) $userId,
                        'sale',
                        'invoice',
                        $invoiceId,
                        'Jewellery voucher sale.',
                        true
                    );
                }
            }

            foreach ($validated['items'] as $item) {
                $pcs = (int) $item['pcs'];
                $touch = (float) ($item['touch'] ?? 0);
                $wastage = (float) ($item['wastage'] ?? 0);
                $netWeight = (float) ($item['net_weight'] ?? 0);
                $fineWeight = round($netWeight * ($touch + $wastage) / 100, 3);
                $product = $this->productSnapshot((int) $userId, (int) $item['product_id']);

                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'product_name_snapshot' => $product->product_name,
                    'product_code_snapshot' => $product->product_code,
                    'pcs' => $pcs,
                    'quantity' => $pcs,
                    'gross_weight' => $item['gross_weight'] ?? null,
                    'net_weight' => $item['net_weight'] ?? null,
                    'touch' => $item['touch'] ?? null,
                    'wastage' => $item['wastage'] ?? null,
                    'fine_weight' => $fineWeight,
                    'unit_price' => 0,
                    'discount_amount' => 0,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'line_total' => 0,
                    'created_at' => $now,
                    'created_by' => $userId,
                ]);
            }
        });

        return redirect()
            ->route('invoice.index')
            ->with('success', $isUpdate ? 'Jewellery voucher saved successfully.' : 'Jewellery voucher created successfully.');
    }

    private function isJewelryUser($userId): bool
    {
        return (bool) DB::table('usermaster')
            ->where('user_id', $userId)
            ->value('is_jewelry');
    }
}