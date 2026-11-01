<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supplier Bill {{ $bill->purchase_number }}</title>
    <style>
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, p { margin: 0; }
        .header { border-bottom: 2px solid #0891b2; margin-bottom: 24px; padding-bottom: 16px; }
        .muted { color: #6b7280; line-height: 1.6; }
        .row { display: table; width: 100%; margin-bottom: 20px; }
        .column { display: table-cell; width: 50%; vertical-align: top; }
        table { border-collapse: collapse; margin-top: 20px; width: 100%; }
        th { background: #f3f4f6; text-align: left; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 9px 8px; }
        .right { text-align: right; }
        .totals { margin-left: auto; margin-top: 16px; width: 250px; }
        .totals td { border: 0; padding: 5px 0; }
        .total { border-top: 2px solid #0891b2 !important; font-size: 14px; font-weight: bold; }
        .notes { margin-top: 24px; white-space: pre-line; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $workspace->company_name ?: $workspace->full_name }}</h1>
        <p class="muted">{{ $workspace->company_address }}</p>
        <p class="muted">{{ $workspace->phone }} @if ($workspace->company_gstin) · GSTIN: {{ $workspace->company_gstin }} @endif</p>
    </div>
    <div class="row">
        <div class="column">
            <h2>{{ $isJewelryUser ? 'Jewellery Supplier Bill' : 'Supplier Bill' }}</h2>
                <p class="muted">Bill No: {{ $bill->purchase_number }}<br>Date: {{ \Carbon\Carbon::parse($bill->purchase_date)->format('d M Y') }}<br>Payment: {{ ucfirst($bill->payment_status) }}@if ($isJewelryUser)<br>Section: {{ $bill->voucher_section ?? 'PURCHASE' }}@endif</p>
        </div>
        <div class="column">
            <h2>{{ $bill->supplier_name }}</h2>
            <p class="muted">{{ $supplier->supplier_address }}<br>{{ $supplier->phone }}<br>{{ $supplier->email }}<br>GSTIN: {{ $supplier->gst_number }}</p>
        </div>
    </div>
    <table>
        <thead>
            @if ($isJewelryUser)
                <tr><th>Product</th><th class="right">Pcs</th><th class="right">Gross wt (g)</th><th class="right">Net wt (g)</th><th class="right">Touch %</th><th class="right">Wastage %</th><th class="right">Fine wt (g)</th></tr>
            @else
                <tr><th>Product</th><th class="right">Qty</th><th class="right">Unit Cost</th><th class="right">Tax</th><th class="right">Discount</th><th class="right">Total</th></tr>
            @endif
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->product_name }} ({{ $item->product_code }})</td>
                    @if ($isJewelryUser)
                        <td class="right">{{ $item->pcs ?? $item->quantity }}</td>
                        <td class="right">{{ $item->gross_weight !== null ? number_format((float) $item->gross_weight, 3) : '—' }}</td>
                        <td class="right">{{ $item->net_weight !== null ? number_format((float) $item->net_weight, 3) : '—' }}</td>
                        <td class="right">{{ $item->touch !== null ? number_format((float) $item->touch, 3) : '—' }}</td>
                        <td class="right">{{ $item->wastage !== null ? number_format((float) $item->wastage, 3) : '—' }}</td>
                        <td class="right">{{ number_format((float) $item->fine_weight, 3) }}</td>
                    @else
                        <td class="right">{{ number_format($item->quantity) }}</td>
                        <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">₹{{ number_format($item->tax_amount, 2) }}</td>
                        <td class="right">₹{{ number_format($item->discount_amount, 2) }}</td>
                        <td class="right">₹{{ number_format($item->line_total, 2) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($isJewelryUser)
        <table class="totals">
            <tr><td>Total Fine</td><td class="right">{{ number_format((float) $items->sum('fine_weight'), 3) }} g</td></tr>
            <tr><td>Less / kasar</td><td class="right">{{ $bill->less_kasar !== null ? number_format((float) $bill->less_kasar, 3).' g' : '—' }}</td></tr>
            <tr><td>Rate cut weight</td><td class="right">{{ $bill->rate_cut_weight !== null ? number_format((float) $bill->rate_cut_weight, 3).' g' : '—' }}</td></tr>
            <tr><td>Other converted fine</td><td class="right">{{ $bill->other_convert_fine !== null ? number_format((float) $bill->other_convert_fine, 3).' g' : '—' }}</td></tr>
            <tr><td>Opening gold · Credit</td><td class="right">{{ $bill->opening_gold !== null ? number_format((float) $bill->opening_gold, 3).' g' : '—' }}</td></tr>
            <tr><td>Closing gold · Debit</td><td class="right">{{ $bill->closing_gold !== null ? number_format((float) $bill->closing_gold, 3).' g' : '—' }}</td></tr>
        </table>
    @else
        <table class="totals">
            <tr><td>Subtotal</td><td class="right">₹{{ number_format($bill->subtotal, 2) }}</td></tr>
            <tr><td>Tax</td><td class="right">₹{{ number_format($bill->total_tax, 2) }}</td></tr>
            <tr><td>Discount</td><td class="right">₹{{ number_format($bill->discount_amount, 2) }}</td></tr>
            <tr><td class="total">Total</td><td class="right total">₹{{ number_format($bill->grand_total, 2) }}</td></tr>
        </table>
    @endif
    @if ($bill->notes)
        <p class="notes"><strong>Notes:</strong><br>{{ $bill->notes }}</p>
    @endif
    @if ($isJewelryUser && $bill->footer_note)
        <p class="notes"><strong>Bill footer note:</strong><br>{{ $bill->footer_note }}</p>
    @endif
</body>
</html>
