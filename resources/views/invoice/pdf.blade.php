<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 18pt 22pt; }
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; margin: 0; }
        table { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        td, th { vertical-align: top; }

        .title td { border: 0; padding: 0 0 3pt 0; }
        .title .main { text-align: center; font-size: 14pt; font-weight: bold; }
        .title .orig { text-align: right; font-style: italic; font-size: 9pt; }

        /* header block: seller / buyer on the left, reference grid on the right */
        .head { border: 1px solid #000; }
        .head > tbody > tr > td { padding: 0; }
        .seller { padding: 4pt 6pt; height: 58pt; border-bottom: 1px solid #000; line-height: 1.35; }
        .buyer  { padding: 4pt 6pt; height: 70pt; line-height: 1.35; }
        .name { font-weight: bold; font-size: 10pt; }
        .small { font-size: 8.5pt; }

        .ref td { padding: 3pt 5pt; height: 24pt; border-bottom: 1px solid #000; font-size: 8.5pt; }
        .ref td.r { border-left: 1px solid #000; }
        .ref tr.last td { border-bottom: 0; }
        .ref .val { font-weight: bold; font-size: 10pt; display: block; margin-top: 2pt; }

        .left-cell  { width: 48%; border-right: 1px solid #000; }
        .right-cell { width: 52%; }

        /* goods table */
        .items { border: 1px solid #000; border-top: 0; }
        .items th { border-left: 1px solid #000; border-bottom: 1px solid #000; padding: 4pt; font-weight: normal; font-size: 9pt; }
        .items th.first, .items td.first { border-left: 0; }
        .items td { border-left: 1px solid #000; padding: 2pt 4pt; font-size: 9pt; }
        .items td.first { border-left: 0; }
        .items .c { text-align: center; }
        .items .num { text-align: right; white-space: nowrap; }
        .items .tax-label { text-align: right; font-weight: bold; font-style: italic; }
        .items .tax-val { font-style: italic; }
        .items .filler td { padding: 0; }
        .items .total td { border-top: 1px solid #000; padding: 3pt 4pt; }
        .items .total .big { font-size: 12pt; font-weight: bold; }
        .bold { font-weight: bold; }

        /* footer */
        .foot { border: 1px solid #000; border-top: 0; }
        .foot td { padding: 3pt 6pt; }
        .words { font-weight: bold; font-size: 9.5pt; padding-top: 3pt; }
        .declare { border-top: 0; }
        .sign { border-left: 1px solid #000; border-top: 1px solid #000; width: 45%; height: 50pt; }

        .computer { text-align: center; font-size: 9pt; margin-top: 5pt; }
    </style>
</head>
<body>

{{-- ===================== TITLE ===================== --}}
<table class="title">
    <tr>
        <td style="width: 20%;">&nbsp;</td>
        <td class="main" style="width: 60%;">INVOICE</td>
        <td class="orig" style="width: 20%;">(Original)</td>
    </tr>
</table>

{{-- ===================== HEADER ===================== --}}
<table class="head">
    <tr>
        <td class="left-cell">
            <div class="seller">
                <span class="name">{{ $seller->company_name ?? config('app.name') }}</span><br>
                {!! nl2br(e($seller->company_address ?? '')) !!}
                @if (!empty($seller->company_gstin))
                    <br>GSTIN - {{ $seller->company_gstin }}
                @endif
            </div>

            <div class="buyer">
                <span class="small">Buyer</span><br>
                <span class="name">{{ $invoice->customer_name }}</span><br>
                {!! nl2br(e($invoice->customer_address ?? '')) !!}
                @if (!empty($invoice->customer_gstin))
                    <br>GSTIN-{{ $invoice->customer_gstin }}
                @endif
            </div>
        </td>

        <td class="right-cell">
            <table class="ref">
                <tr>
                    <td style="width: 50%;">Invoice No.<span class="val">{{ $invoice->invoice_number }}</span></td>
                    <td class="r" style="width: 50%;">Dated<span class="val">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('j-M-Y') }}</span></td>
                </tr>
                <tr>
                    <td>Delivery Note</td>
                    <td class="r">Mode/Terms of Payment</td>
                </tr>
                <tr>
                    <td>Supplier's Ref.</td>
                    <td class="r">Other Reference(s)</td>
                </tr>
                <tr>
                    <td>Buyer's Order No.</td>
                    <td class="r">Dated</td>
                </tr>
                <tr>
                    <td>Despatch Document No.</td>
                    <td class="r">Dated</td>
                </tr>
                <tr>
                    <td>Despatched through</td>
                    <td class="r">Destination</td>
                </tr>
                <tr class="last">
                    <td colspan="2" style="height: 34pt;">Terms of Delivery</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ===================== GOODS ===================== --}}
<table class="items">
    <thead>
        <tr>
            <th class="first" style="width: 5%;">Sl<br>No.</th>
            <th style="width: 43%;">Description of Goods</th>
            <th style="width: 15%;">Quantity</th>
            <th style="width: 13%;">Rate</th>
            <th style="width: 6%;">per</th>
            <th style="width: 18%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $i => $item)
            <tr>
                <td class="first c">{{ $i + 1 }}</td>
                <td class="bold">{{ $item->product_name }}</td>
                <td class="num bold">{{ number_format($item->quantity, 3, '.', '') }} {{ $item->unit }}</td>
                <td class="num">{{ \App\Support\IndianFormat::money($item->unit_price) }}</td>
                <td>{{ $item->unit }}</td>
                <td class="num bold">{{ \App\Support\IndianFormat::money($item->line_total) }}</td>
            </tr>
        @endforeach

        @if ((float) $invoice->discount_amount > 0)
            <tr>
                <td class="first">&nbsp;</td>
                <td class="tax-label">Less : Discount</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="num tax-val">(-) {{ \App\Support\IndianFormat::money($invoice->discount_amount) }}</td>
            </tr>
        @endif

        @foreach ($taxRows as $tax)
            <tr>
                <td class="first">&nbsp;</td>
                <td class="tax-label">{{ $tax['label'] }}</td>
                <td>&nbsp;</td>
                <td class="num tax-val">{{ number_format($tax['rate'], 2) }}</td>
                <td class="tax-val">%</td>
                <td class="num bold">{{ \App\Support\IndianFormat::money($tax['amount']) }}</td>
            </tr>
        @endforeach

        {{-- empty space so the table keeps its height like the original bill --}}
        <tr class="filler">
            <td class="first" style="height: {{ $fillerHeight }}pt;">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        <tr class="total">
            <td class="first">&nbsp;</td>
            <td class="num">Total</td>
            <td class="num bold" style="font-size: 10pt;">
                @if ($totalQty !== null)
                    {{ number_format($totalQty, 3, '.', '') }} {{ $totalUnit }}
                @endif
            </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="num big">{{ \App\Support\IndianFormat::money($invoice->grand_total) }}</td>
        </tr>
    </tbody>
</table>

{{-- ===================== FOOTER ===================== --}}
<table class="foot" style="border-bottom: 0;">
    <tr>
        <td style="height: 60pt;">
            Amount Chargeable (in words)
            <div class="words">{{ $amountInWords }}</div>
        </td>
        <td style="text-align: right; width: 20%; padding-top: 1pt;" class="small">E. &amp; O.E</td>
    </tr>
</table>

<table class="foot" style="margin-top: 0;">
    <tr>
        <td style="border-top: 0; width: 55%; padding-bottom: 6pt;">
            @if (!empty($invoice->customer_gstin))
                Buyer's Local Sales Tax No. : <span class="bold">{{ $invoice->customer_gstin }}</span><br>
            @endif
            Declaration<br>
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
        </td>
        <td class="sign">
            <div style="text-align: right;" class="bold">for {{ $seller->company_name ?? config('app.name') }}</div>
            <div style="text-align: right; margin-top: 22pt;">Authorised Signatory</div>
        </td>
    </tr>
</table>

<div class="computer">This is a Computer Generated Invoice</div>

</body>
</html>