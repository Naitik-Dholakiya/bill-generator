<?php

namespace App\Http\Controllers;

use App\Support\IndianFormat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceGeneratorController extends Controller
{
    /**
     * GET /invoices/{invoice}/pdf          -> opens the PDF in the browser
     * GET /invoices/{invoice}/pdf?download=1 -> forces a download
     */
    public function generate(Request $request, int $invoiceId)
    {
        $user = Cookie::get('GTA') ? $this->currentUser($request) : null;

        $invoice = DB::table('invoicemaster as i')
            ->join('customermaster as c', 'c.customer_id', '=', 'i.customer_id')
            ->where('i.invoice_id', $invoiceId)
            ->whereNull('i.deleted_at')
            ->select(
                'i.*',
                'c.customer_name',
                'c.billing_address as customer_address',
                'c.gst_number as customer_gstin'
            )
            ->first();

        abort_if(!$invoice, 404, 'Invoice not found.');

        abort_unless(
            $this->isSuperAdmin($user)
                || ($user !== null && (int) $invoice->created_by === (int) $user->user_id),
            403
        );

        // The seller is the user who created the invoice; their template is used.
        $seller = DB::table('usermaster')->where('user_id', $invoice->created_by)->first();

        $items = DB::table('invoice_items as it')
            ->join('productmaster as p', 'p.product_id', '=', 'it.product_id')
            ->where('it.invoice_id', $invoiceId)
            ->whereNull('it.deleted_at')
            ->orderBy('it.item_id')
            ->select('it.*', 'p.product_name')
            ->get();

        // ---- GST rows (CGST + SGST within the same state, otherwise IGST) ----
        $sellerGstin = $seller->company_gstin ?? null;
        $buyerGstin  = $invoice->customer_gstin;
        $interState  = $sellerGstin && $buyerGstin
            && substr($sellerGstin, 0, 2) !== substr($buyerGstin, 0, 2);

        $taxRows = [];
        $byRate  = $items->where('tax_percent', '>', 0)
            ->groupBy(fn ($row) => number_format((float) $row->tax_percent, 2, '.', ''));

        foreach ($byRate as $rate => $rows) {
            $taxAmount = round((float) $rows->sum('tax_amount'), 2);

            if ($interState) {
                $taxRows[] = ['label' => 'IGST', 'rate' => (float) $rate, 'amount' => $taxAmount];
            } else {
                $half = round($taxAmount / 2, 2);
                $taxRows[] = ['label' => 'CGST', 'rate' => (float) $rate / 2, 'amount' => $half];
                $taxRows[] = ['label' => 'SGST', 'rate' => (float) $rate / 2, 'amount' => $taxAmount - $half];
            }
        }

        // ---- Total quantity (only meaningful when every line uses the same unit) ----
        $units    = $items->pluck('unit')->unique();
        $totalQty = $units->count() === 1 ? (float) $items->sum('quantity') : null;

        // ---- Keep the item area tall (but never taller than one A4 page allows) ----
        $usedRows     = $items->count() + count($taxRows) + ((float) $invoice->discount_amount > 0 ? 1 : 0);
        $fillerHeight = max(8, 280 - ($usedRows * 18)); // points; shrinks as rows are added so the bill stays on one page

        $data = [
            'invoice'      => $invoice,
            'seller'       => $seller,
            'items'        => $items,
            'taxRows'      => $taxRows,
            'totalQty'     => $totalQty,
            'totalUnit'    => $units->count() === 1 ? $units->first() : null,
            'amountInWords' => IndianFormat::rupeesInWords($invoice->grand_total),
            'fillerHeight' => $fillerHeight,
        ];

        $pdf = Pdf::loadView($this->resolveView($seller->invoice_template ?? null), $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $invoice->invoice_number) . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    /**
     * PUT /admin/users/{user}/invoice-template
     * Super Admin only: choose which invoice template another user gets.
     */
    public function updateTemplate(Request $request, int $userId)
    {
        abort_unless($this->isSuperAdmin($this->currentUser($request)), 403, 'Only Super Admin can change invoice templates.');

        $validated = $request->validate([
            'invoice_template' => ['required', 'string', Rule::in(array_keys(config('invoice.templates')))],
        ]);

        abort_unless(DB::table('usermaster')->where('user_id', $userId)->exists(), 404, 'User not found.');

        DB::table('usermaster')->where('user_id', $userId)->update([
            'invoice_template' => $validated['invoice_template'],
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'Invoice template updated.');
    }

    // ---------------------------------------------------------------------

    /**
     * Logged-in user = whatever the app stored in the "GTA" cookie.
     * Returns the matching usermaster row (or null if missing / unknown).
     * The cookie may hold the user_id or the email; both are handled.
     */
    private function currentUser(Request $request): ?object
    {
        $id = Cookie::get('GTA');

        if (!$id || !is_scalar($id)) {
            return null;
        }

        $query = DB::table('usermaster');

        return str_contains((string) $id, '@')
            ? $query->where('email', $id)->first()
            : $query->where('user_id', $id)->first();
    }

    private function isSuperAdmin(?object $user): bool
    {
        return $user !== null && $user->is_admin === 'Y';
    }

    /** Only whitelisted templates can be rendered; anything else falls back to the default. */
    private function resolveView(?string $key): string
    {
        $templates = config('invoice.templates');

        return ($templates[$key] ?? $templates[config('invoice.default')])['view'];
    }
}