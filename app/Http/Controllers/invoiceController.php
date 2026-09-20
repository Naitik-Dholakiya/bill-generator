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


    /**
     * Display create invoice page.
     */
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
        return view('invoice.create',compact('customers','products','invoiceNumber'));
        }


    /**
     * Generate unique invoice number.
     *
     * Example:
     * INV202600001
     * INV202600002
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');

        $userId = Cookie::get('GTA');
        $prefix = 'INV' . $year .$userId;


        // Get Last Invoice Number
    
        $lastInvoice = DB::table('invoicemaster')
            ->where('invoice_number', 'LIKE', $prefix . '%')
            ->orderByDesc('invoice_id')
            ->value('invoice_number');
        // Generate Sequence

        if ($lastInvoice) {

            $lastSequence = (int) substr(
                $lastInvoice,
                strlen($prefix)
            );

            $sequence = $lastSequence + 1;

        } else {
            $sequence = 1;
        }
        //  Final Invoice Number
        return $prefix . str_pad(
            $sequence,
            5,
            '0',
            STR_PAD_LEFT
        );
    }


    /**
     * Save invoice and generate PDF.
     */
    public function generatePdf(Request $request)
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

            /*
            |--------------------------------------------------------------------------
            | Generate Fresh Invoice Number
            |--------------------------------------------------------------------------
            |
            | Do not completely trust the invoice number coming from the
            | readonly HTML input.
            |
            */

            $invoiceNumber = $this->generateInvoiceNumber();


            /*
            |--------------------------------------------------------------------------
            | Calculate Totals Again on Server
            |--------------------------------------------------------------------------
            |
            | Never trust totals calculated by JavaScript.
            |
            */

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


                /*
                |--------------------------------------------------------------------------
                | Item Subtotal
                |--------------------------------------------------------------------------
                */

                $itemSubtotal =
                    $quantity * $unitPrice;


                /*
                |--------------------------------------------------------------------------
                | Item Total
                |--------------------------------------------------------------------------
                */

                $itemTotal =
                    $itemSubtotal
                    + $taxAmount
                    - $discountAmount;


                $itemTotal =
                    max($itemTotal, 0);


                /*
                |--------------------------------------------------------------------------
                | Add To Invoice Totals
                |--------------------------------------------------------------------------
                */

                $subtotal += $itemSubtotal;

                $totalTax += $taxAmount;

                $totalDiscount += $discountAmount;

                $grandTotal += $itemTotal;
            }


            /*
            |--------------------------------------------------------------------------
            | Round Financial Values
            |--------------------------------------------------------------------------
            */

            $subtotal = round($subtotal, 2);

            $totalTax = round($totalTax, 2);

            $totalDiscount = round($totalDiscount, 2);

            $grandTotal = round($grandTotal, 2);


            /*
            |--------------------------------------------------------------------------
            | Insert Invoice Master
            |--------------------------------------------------------------------------
            */

            $invoiceId = DB::table('invoicemaster')->insertGetId([

                'invoice_number' => $invoiceNumber,

                'customer_id' => $validated['customer_id'],

                'user_id' => $userId,

                'invoice_date' => $validated['invoice_date'],

                'subtotal' => $subtotal,

                'total_tax' => $totalTax,

                'discount_amount' => $totalDiscount,

                'grand_total' => $grandTotal,

                'payment_status' => 'pending',

                'notes' => $validated['notes'] ?? null,

                'created_at' => now(),

                'updated_at' => now(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Insert Invoice Items
            |--------------------------------------------------------------------------
            */

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


                DB::table('invoice_items')->insert([

                    'invoice_id' => $invoiceId,

                    'product_id' => $item['product_id'],

                    'quantity' => $quantity,

                    'unit_price' => $unitPrice,

                    'tax_amount' => $taxAmount,

                    'discount_amount' => $discountAmount,

                    'total_amount' => round(
                        $itemTotal,
                        2
                    ),

                    'created_at' => now(),

                    'updated_at' => now(),

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Commit Transaction
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | Generate PDF
            |--------------------------------------------------------------------------
            */

            return $this->pdf($invoiceId);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Rollback Database
            |--------------------------------------------------------------------------
            */

            DB::rollBack();


            /*
            |--------------------------------------------------------------------------
            | Log Error
            |--------------------------------------------------------------------------
            */

            report($e);


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create invoice. ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * Generate invoice PDF.
     */
    public function pdf($invoiceId)
    {
        /*
        |--------------------------------------------------------------------------
        | Get Invoice
        |--------------------------------------------------------------------------
        */

        $invoice = DB::table('invoicemaster')
            ->leftJoin(
                'customermaster',
                'invoicemaster.customer_id',
                '=',
                'customermaster.customer_id'
            )
            ->leftJoin(
                'usermaster',
                'invoicemaster.user_id',
                '=',
                'usermaster.user_id'
            )
            ->select(
                'invoicemaster.*',
                'customermaster.customer_name',
                'customermaster.phone',
                'customermaster.email',
                'customermaster.shipping_address',
                'usermaster.user_id as created_user_id'
            )
            ->where(
                'invoicemaster.invoice_id',
                $invoiceId
            )
            ->whereNull('invoicemaster.deleted_at')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Invoice Not Found
        |--------------------------------------------------------------------------
        */

        if (!$invoice) {

            abort(
                404,
                'Invoice not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Invoice Items
        |--------------------------------------------------------------------------
        */

        $items = DB::table('invoice_items')
            ->join(
                'productmaster',
                'invoice_items.product_id',
                '=',
                'productmaster.product_id'
            )
            ->select(
                'invoice_items.*',
                'productmaster.product_name',
                'productmaster.product_code',
                // 'productmaster.sku'
            )
            ->where(
                'invoice_items.invoice_id',
                $invoiceId
            )
            ->whereNull('invoice_items.deleted_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'invoice.pdf',
            compact(
                'invoice',
                'items'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | PDF Settings
        |--------------------------------------------------------------------------
        */

        $pdf->setPaper(
            'A4',
            'portrait'
        );


        /*
        |--------------------------------------------------------------------------
        | Download PDF
        |--------------------------------------------------------------------------
        */

        return $pdf->stream(
            $invoice->invoice_number . '.pdf'
        );
    }
}