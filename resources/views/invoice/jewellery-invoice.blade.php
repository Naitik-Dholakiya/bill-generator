{{--
    resources/views/jewellery-invoice.blade.php

    Browser preview / print:
        return view('jewellery-invoice', ['invoice' => $invoice]);

    PDF (dompdf / snappy etc.) - pass pdf => true so the Print button is not rendered:
        Pdf::loadView('jewellery-invoice', ['invoice' => $invoice, 'pdf' => true]);

    Expected $invoice shape (array or object cast to array):
    [
        'shop_name', 'party_name', 'city', 'mobile', 'phone', 'time', 'voucher_no', 'date',
        'section', 'items' => [[name, extra, pcs, gross_wt, net_wt, touch, wastage, fine, amount]],
        'less_kasar', 'rate_cut_weight', 'rate', 'rate_cut_amt', 'oth_convert_fine', 'sub_total',
        'remarks', 'opening_bal', 'opening_gold', 'closing_bal', 'closing_gold',
        'closing_gold_type' (Dr / Cr), 'footer_note'
    ]
--}}
@php
    $inv = is_array($invoice ?? null) ? $invoice : (array) ($invoice ?? []);
    $isPdf = !empty($pdf);

    $items = collect($inv['items'] ?? []);

    $fmt = function ($v, $dec = 3) {
        return ($v === null || $v === '') ? '' : number_format((float) $v, $dec, '.', '');
    };

    $date = $inv['date'] ?? '';
    if ($date instanceof \DateTimeInterface) {
        $date = $date->format('d-m-Y');
    }

    $totalGross = $items->sum(fn ($i) => (float) ($i['gross_wt'] ?? 0));
    $totalNet   = $items->sum(fn ($i) => (float) ($i['net_wt'] ?? 0));
    $totalFine  = $items->sum(fn ($i) => (float) ($i['fine'] ?? 0));
    $totalPcs   = $items->sum(fn ($i) => (int) ($i['pcs'] ?? 0));
    $totalAmt   = $items->sum(fn ($i) => (float) ($i['amount'] ?? 0));

    $closingGold = $inv['closing_gold'] ?? null;
    if ($closingGold === null && $totalFine) {
        $closingGold = $fmt($totalFine);
    }
    $closingType = $inv['closing_gold_type'] ?? 'Dr';

    $section = strtoupper($inv['section'] ?? 'SALES');

    // pad with blank rows so the invoice body always looks like a full voucher
    $minRows = 10;
    $padRows = max(0, $minRows - $items->count());
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voucher #{{ $inv['voucher_no'] ?? '' }}</title>
    <style>
        /* A4 with 8mm margins -> 194mm usable width. The browser/PDF engine
           applies the margin, so nothing can overflow on the right. */
        @page { size: A4 portrait; margin: 8mm; }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .sheet { width: 100%; }

        /* All layout uses tables (works in browsers AND dompdf; no flex needed) */
        table { border-collapse: collapse; border-spacing: 0; width: 100%; }
        td, th { vertical-align: top; }

        .voucher { border: 1px solid #000; page-break-inside: avoid; }

        /* ---------- Header ---------- */
        .head { border-bottom: 1px solid #000; }
        .head td { padding: 6px 8px; }
        .head .tag {
            display: inline-block;
            border: 1.5px solid #000;
            padding: 2px 10px;
            font-size: 20px;
            font-weight: bold;
        }
        .head .shop {
            font-family: Georgia, "Times New Roman", serif;
            font-weight: bold;
            font-size: 16px;
            margin-left: 8px;
        }
        .head .party { font-size: 18px; font-weight: bold; margin-top: 6px; }
        .head .city  { font-size: 13px; margin-top: 2px; }
        .head .contact {
            text-align: right;
            vertical-align: bottom;
            font-size: 14px;
            font-weight: bold;
        }

        /* ---------- Meta row ---------- */
        .meta { border-bottom: 1px solid #000; }
        .meta td { padding: 6px 8px; font-size: 13px; }
        .meta b { font-size: 16px; }

        /* ---------- Items ---------- */
        table.items { table-layout: fixed; }
        table.items th,
        table.items td {
            border-left: 1px solid #000;
            padding: 3px 4px;
            font-size: 12px;
            overflow: hidden;
        }
        table.items th:first-child,
        table.items td:first-child { border-left: 0; }
        table.items thead th {
            border-bottom: 1px solid #000;
            font-weight: bold;
            font-size: 12px;
            text-align: right;
            padding: 5px 4px;
        }
        table.items thead th.l { text-align: left; }
        .r { text-align: right; }
        .c { text-align: center; }

        .sec-title { font-weight: bold; text-decoration: underline; font-size: 13px; }
        .item-row td { height: 26px; vertical-align: middle; }
        .item-name  { font-weight: bold; }
        .item-extra { float: right; font-weight: normal; }

        .total-row td { border-top: 1px solid #000; font-weight: bold; height: 26px; vertical-align: middle; }
        .gross-row td { border-top: 3px double #000; border-bottom: 1px solid #000; font-weight: bold; height: 28px; vertical-align: middle; }

        .pcs-circle {
            display: inline-block;
            min-width: 22px;
            height: 22px;
            line-height: 18px;
            border: 1.5px solid #1b2a6b;
            border-radius: 11px;
            text-align: center;
            font-size: 11px;
            color: #1b2a6b;
        }

        /* ---------- Calculation block ---------- */
        table.calc { margin-top: 6px; }
        table.calc td { padding: 3px 0; font-size: 13px; }
        table.calc .k { width: 42%; text-align: right; padding-right: 8px; }
        table.calc .v { width: 22%; font-weight: bold; text-align: left; }
        table.calc .sp { width: 36%; }
        table.calc tr.rule td { border-top: 1px solid #000; }
        table.calc tr.rule td.sp { border-top: 0; }

        /* ---------- Footer ---------- */
        .remarks {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding: 8px;
            font-style: italic;
            font-size: 13px;
            min-height: 34px;
        }
        table.balances { border-top: 1px solid #000; }
        table.balances td { padding: 6px 8px; font-weight: bold; font-size: 15px; line-height: 1.6; }
        table.balances td.right { width: 50%; }

        table.signs { border-top: 1px solid #000; }
        table.signs td { padding: 12px 8px 26px; font-size: 13px; }

        .footer-note {
            text-align: center;
            font-size: 11px;
            font-style: italic;
            padding-top: 6px;
        }

        /* ---------- Screen-only preview ---------- */
        .toolbar { text-align: center; padding: 10px; }
        .toolbar button { padding: 8px 22px; font-size: 15px; cursor: pointer; }

        @media screen {
            body { background: #e9e9e9; }
            .sheet {
                width: 194mm;
                max-width: 100%;
                margin: 10px auto;
                padding: 8mm;
                background: #fff;
                box-sizing: content-box;
            }
        }

        @media print {
            .toolbar { display: none !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

@unless ($isPdf)
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
    </div>
@endunless

<div class="sheet">
    <div class="voucher">

        {{-- Header --}}
        <table class="head">
            <tr>
                <td style="width:60%;">
                    <span class="tag">VOUCHER</span><span class="shop">{{ $inv['shop_name'] ?? '' }}</span>
                    <div class="party">{{ $inv['party_name'] ?? '' }}</div>
                    <div class="city">{{ $inv['city'] ?? '' }}</div>
                </td>
                <td class="contact" style="width:40%;">
                    M : {{ $inv['mobile'] ?? '' }}, Ph : {{ $inv['phone'] ?? '' }}
                </td>
            </tr>
        </table>

        {{-- Time / No / Date --}}
        <table class="meta">
            <tr>
                <td style="width:33%;">No : <b>{{ $inv['voucher_no'] ?? '' }}</b></td>
                <td style="width:34%; text-align:center; font-weight:bold;">{{ $inv['time'] ?? '' }}</td>
                <td style="width:33%; text-align:right;">Date : <b>{{ $date }}</b></td>
            </tr>
        </table>

        {{-- Items (column widths add up to exactly 100%) --}}
        <table class="items">
            <colgroup>
                <col style="width:5%">
                <col style="width:23%">
                <col style="width:7%">
                <col style="width:12%">
                <col style="width:12%">
                <col style="width:9%">
                <col style="width:8%">
                <col style="width:12%">
                <col style="width:12%">
            </colgroup>
            <thead>
                <tr>
                    <th class="l">Sr.</th>
                    <th class="l">Item Description</th>
                    <th>Pcs</th>
                    <th>G. Wt</th>
                    <th>Net Wt</th>
                    <th>Touch</th>
                    <th>Wst</th>
                    <th>Fine</th>
                    <th>Amt.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td colspan="8"><span class="sec-title">{{ $section }}</span></td>
                </tr>

                @foreach ($items as $i => $item)
                    <tr class="item-row">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="item-name">{{ $item['name'] ?? '' }}</span>
                            @if (isset($item['extra']))
                                <span class="item-extra">{{ $item['extra'] }}</span>
                            @endif
                        </td>
                        <td class="c">
                            @if (!empty($item['pcs']))
                                <span class="pcs-circle">{{ $item['pcs'] }}</span>
                            @endif
                        </td>
                        <td class="r">{{ $fmt($item['gross_wt'] ?? null) }}</td>
                        <td class="r">{{ $fmt($item['net_wt'] ?? null) }}</td>
                        <td class="r">{{ $fmt($item['touch'] ?? null, 2) }}</td>
                        <td class="r">{{ $fmt($item['wastage'] ?? null, 2) }}</td>
                        <td class="r">{{ $fmt($item['fine'] ?? null) }}</td>
                        <td class="r">{{ $fmt($item['amount'] ?? null, 2) }}</td>
                    </tr>
                @endforeach

                @for ($p = 0; $p < $padRows; $p++)
                    <tr class="item-row">
                        <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                @endfor

                <tr class="total-row">
                    <td></td>
                    <td>{{ $section }} TOTAL :</td>
                    <td class="c">{{ $totalPcs ?: '' }}</td>
                    <td class="r">{{ $fmt($totalGross) }}</td>
                    <td class="r">{{ $fmt($totalNet) }}</td>
                    <td></td>
                    <td></td>
                    <td class="r">{{ $fmt($totalFine) }}</td>
                    <td class="r">{{ $totalAmt ? $fmt($totalAmt, 2) : '' }}</td>
                </tr>

                <tr class="gross-row">
                    <td></td>
                    <td>Gross Total :</td>
                    <td></td>
                    <td class="r">{{ $fmt($totalGross) }}</td>
                    <td class="r">{{ $fmt($totalNet) }}</td>
                    <td></td>
                    <td></td>
                    <td class="r">{{ $fmt($totalFine) }}</td>
                    <td class="r">{{ $totalAmt ? $fmt($totalAmt, 2) : '' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Calculation block --}}
        <table class="calc">
            <tr><td class="sp"></td><td class="k">Less/Kasar :</td><td class="v">{{ $inv['less_kasar'] ?? '' }}</td></tr>
            <tr><td class="sp"></td><td class="k">Rate Cut Weight :</td><td class="v">{{ $inv['rate_cut_weight'] ?? '' }}</td></tr>
            <tr><td class="sp"></td><td class="k">Rate :</td><td class="v">{{ $inv['rate'] ?? '' }}</td></tr>
            <tr><td class="sp"></td><td class="k">Rate Cut Amt :</td><td class="v">{{ $inv['rate_cut_amt'] ?? '' }}</td></tr>
            <tr><td class="sp"></td><td class="k">Oth. Convert to Fine :</td><td class="v">{{ $inv['oth_convert_fine'] ?? '' }}</td></tr>
            <tr class="rule"><td class="sp"></td><td class="k">Sub Total :</td><td class="v">{{ $inv['sub_total'] ?? '' }}</td></tr>
        </table>

        {{-- Remarks --}}
        <div class="remarks">Remarks : {{ $inv['remarks'] ?? '' }}</div>

        {{-- Balances --}}
        <table class="balances">
            <tr>
                <td>
                    <div>Opening Bal : {{ $inv['opening_bal'] ?? '' }}</div>
                    <div>Opening Gold : {{ $inv['opening_gold'] ?? '' }}</div>
                </td>
                <td class="right">
                    <div>Closing Bal : {{ $inv['closing_bal'] ?? '' }}</div>
                    <div>Closing Gold :
                        @if ($closingGold !== null && $closingGold !== '')
                            {{ $closingGold }} {{ $closingType }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Signatures --}}
        <table class="signs">
            <tr>
                <td style="width:50%;">Receiver's Signature</td>
                <td style="width:50%; text-align:right;">Authorised Signature</td>
            </tr>
        </table>
    </div>

    <div class="footer-note">{{ $inv['footer_note'] ?? 'This is a computer generated invoice' }}</div>
</div>

</body>
</html>