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
            <h2>Supplier Bill</h2>
            <p class="muted">Bill No: {{ $bill->purchase_number }}<br>Date: {{ \Carbon\Carbon::parse($bill->purchase_date)->format('d M Y') }}<br>Payment: {{ ucfirst($bill->payment_status) }}</p>
        </div>
        <div class="column">
            <h2>{{ $bill->supplier_name }}</h2>
            <p class="muted">{{ $supplier->supplier_address }}<br>{{ $supplier->phone }}<br>{{ $supplier->email }}<br>GSTIN: {{ $supplier->gst_number }}</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="right">Qty</th>
                <th class="right">Unit Cost</th>
                <th class="right">Tax</th>
                <th class="right">Discount</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->product_name }} ({{ $item->product_code }})</td>
                    <td class="right">{{ number_format($item->quantity) }}</td>
                    <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₹{{ number_format($item->tax_amount, 2) }}</td>
                    <td class="right">₹{{ number_format($item->discount_amount, 2) }}</td>
                    <td class="right">₹{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="totals">
        <tr><td>Subtotal</td><td class="right">₹{{ number_format($bill->subtotal, 2) }}</td></tr>
        <tr><td>Tax</td><td class="right">₹{{ number_format($bill->total_tax, 2) }}</td></tr>
        <tr><td>Discount</td><td class="right">₹{{ number_format($bill->discount_amount, 2) }}</td></tr>
        <tr><td class="total">Total</td><td class="right total">₹{{ number_format($bill->grand_total, 2) }}</td></tr>
    </table>
    @if ($bill->notes)
        <p class="notes"><strong>Notes:</strong><br>{{ $bill->notes }}</p>
    @endif
</body>
</html>
