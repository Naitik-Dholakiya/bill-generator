<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display invoice list.
     */
    public function index()
    {
        $invoices = DB::table('invoicemaster')
            ->leftJoin(
                'customermaster',
                'invoicemaster.customer_id',
                '=',
                'customermaster.customer_id'
            )
            ->select(
                'invoicemaster.*',
                'customermaster.customer_name'
            )
            ->whereNull('invoicemaster.deleted_at')
            ->orderByDesc('invoicemaster.invoice_id')
            ->paginate(15);

        return view('invoice.index', compact('invoices'));
    }

    public function create()
    {
        //Get Active Customer
        $customers = DB::table('customermaster')
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
        $formItems =[];

        return view('invoice.create', compact('customers', 'products', 'invoiceNumber', 'formItems'));
    }

    public function edit(int $invoiceId)
    {
        $invoice = DB::table('invoicemaster')
            ->where('invoice_id', $invoiceId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');
        $items = DB::table('invoice_items')
            ->where('invoice_id', $invoiceId)
            ->whereNull('deleted_at')
            ->orderBy('item_id')
            ->get();

        $customers = DB::table('customermaster')
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

        if ($items->isEmpty()) {
            $items = collect([null]);
        }

        return view('invoice.edit', compact('invoice', 'items', 'customers', 'products'));
    }

    public function view(int $invoiceId)
    {
        $invoice = DB::table('invoicemaster as i')
            ->leftJoin('customermaster as c', 'c.customer_id', '=', 'i.customer_id')
            ->where('i.invoice_id', $invoiceId)
            ->whereNull('i.deleted_at')
            ->select('i.*', 'c.customer_name', 'c.phone', 'c.email', 'c.billing_address', 'c.gst_number')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');

        $items = DB::table('invoice_items as it')
            ->leftJoin('productmaster as p', 'p.product_id', '=', 'it.product_id')
            ->where('it.invoice_id', $invoiceId)
            ->whereNull('it.deleted_at')
            ->orderBy('it.item_id')
            ->select('it.*', 'p.product_name')
            ->get();

        return view('invoice.view', compact('invoice', 'items'));
    }

    public function delete(int $invoiceId)
    {
        $invoice = DB::table('invoicemaster')
            ->where('invoice_id', $invoiceId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');

        DB::transaction(function () use ($invoiceId) {
            $now = now();

            DB::table('invoicemaster')
                ->where('invoice_id', $invoiceId)
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
        $invoice = DB::table('invoicemaster')
            ->where('invoice_id', $invoiceId)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customermaster,customer_id'],
            'invoice_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:productmaster,product_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $userId = Cookie::get('GTA');

        DB::transaction(function () use ($validated, $invoiceId, $userId) {
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
        // Validate Request
        $validated = $request->validate([

            'invoice_number' => [
                'required',
                'string',
                'max:50'
            ],

            'customer_id' => [
                'required',
                'integer',
                'exists:customermaster,customer_id'
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

        ]);
        // Current Logged-In User
        $userId = Cookie::get('GTA');;

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
}