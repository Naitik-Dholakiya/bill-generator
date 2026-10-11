<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

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
                'customermaster.customer_name',
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
            ->where('status', '1')
            // ->whereNull('deleted_at')
            ->orderBy('product_name')
            ->get();


        // Generate Invoice Number
        $invoiceNumber = $this->generateInvoiceNumber();
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
                $query->where('status', '1')
                    ->orWhereIn('product_id', $productIds);
            })
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
            ->select('i.*', 'c.customer_name', 'c.phone', 'c.email', 'c.billing_address', 'c.gst_number')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');
        $isJewelryUser = $this->isJewelryUser($userId);

        $items = DB::table('invoice_items as it')
            ->leftJoin('productmaster as p', 'p.product_id', '=', 'it.product_id')
            ->where('it.invoice_id', $invoiceId)
            ->whereNull('it.deleted_at')
            ->orderBy('it.item_id')
            ->select('it.*', 'p.product_name')
            ->get();

        $view = $isJewelryUser ? 'invoice.jewellery.view' : 'invoice.view';
        $rows = $items;

        return view($view, compact('invoice', 'items', 'rows', 'isJewelryUser'));
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
            'items.*.product_id' => ['required', 'integer', 'exists:productmaster,product_id'],
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
                    'grand_total' => round($grandTotal, 2),
                    'notes' => $validated['notes'] ?? null,
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

                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
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
                'exists:productmaster,product_id'
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
            $invoiceNumber = $this->generateInvoiceNumber();

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
                'notes' => $validated['notes'] ?? null,
                'created_at' => now(),
                'created_by' => $userId,
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

                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
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

    private function generateInvoiceNumber()
    {
        $year = date('Y');

        $userId = Cookie::get('GTA');
        $prefix = 'INV' . $year .$userId;

        $lastInvoice = DB::table('invoicemaster')
            ->where('invoice_number', 'LIKE', $prefix . '%')
            ->orderByDesc('invoice_id')
            ->value('invoice_number');

        if ($lastInvoice) {

            $lastSequence = (int) substr(
                $lastInvoice,
                strlen($prefix)
            );

            $sequence = $lastSequence + 1;

        } else {
            $sequence = 1;
        }

        return $prefix . str_pad($sequence,5,'0',STR_PAD_LEFT);
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
            'items.*.product_id' => ['required', 'integer', 'exists:productmaster,product_id'],
            'items.*.pcs' => ['required', 'integer', 'min:1'],
            'items.*.gross_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.net_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.touch' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'],
            'items.*.wastage' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,3'],
        ]);

        $now = now();

        DB::transaction(function () use ($validated, $userId, $invoiceId, $now) {
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
                'updated_at' => $now,
            ];

            if ($invoiceId === null) {
                $invoiceData['invoice_number'] = $this->generateInvoiceNumber();
                $invoiceData['created_at'] = $now;
                $invoiceData['created_by'] = $userId;
                $invoiceId = DB::table('invoicemaster')->insertGetId($invoiceData);
            } else {
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

            foreach ($validated['items'] as $item) {
                $pcs = (int) $item['pcs'];
                $touch = (float) ($item['touch'] ?? 0);
                $wastage = (float) ($item['wastage'] ?? 0);
                $netWeight = (float) ($item['net_weight'] ?? 0);
                $fineWeight = round($netWeight * ($touch + $wastage) / 100, 3);

                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
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