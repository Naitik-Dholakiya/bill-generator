@php
    // ---------- Normalise input (array / object / model / collection) ----------
    $toArray = function ($v) {
        if (is_array($v)) return $v;
        if (is_object($v) && method_exists($v, 'toArray')) return $v->toArray();
        if (is_object($v)) return get_object_vars($v);
        return [];
    };

    $inv   = $toArray($invoice ?? []);
    $isPdf = !empty($pdf);

    $items = collect($inv['items'] ?? [])->map(fn ($i) => $toArray($i))->values();

    // ---------- Field mapping (new names, with old names as fallback) ----------
    $sellerName   = $inv['seller_name']   ?? $inv['shop_name']   ?? '';
    $customerName = $inv['customer_name'] ?? $inv['party_name']  ?? '';
    $customerNo   = $inv['customer_no']   ?? $inv['mobile']      ?? '';
    $sellerNo     = $inv['seller_no']     ?? $inv['phone']       ?? '';
    $invoiceNo    = $inv['invoice_no']    ?? $inv['voucher_no']  ?? '';

    // ---------- Formatters ----------
    // Plain fixed decimals (weights, touch, wastage, fine)
    $fmt = function ($v, $dec = 3) {
        return is_numeric($v) ? number_format((float) $v, $dec, '.', '') : '';
    };

    // Indian digit grouping (12,34,567.00) for money
    $inr = function ($v, $dec = 2) {
        if (!is_numeric($v)) return '';
        $n   = (float) $v;
        $neg = $n < 0;
        $s   = number_format(abs($n), $dec, '.', '');
        [$int, $frac] = array_pad(explode('.', $s), 2, '');
        if (strlen($int) > 3) {
            $last3 = substr($int, -3);
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($int, 0, -3));
            $int   = $rest . ',' . $last3;
        }
        return ($neg ? '-' : '') . $int . ($dec ? '.' . $frac : '');
    };

    // Free-form values: format if numeric, otherwise print as given
    $val = function ($v, $dec = 2) use ($inr) {
        if ($v === null || $v === '') return '';
        return is_numeric($v) ? $inr($v, $dec) : (string) $v;
    };

    // Pieces: 1.000 -> 1, 2.500 -> 2.5
    $pcsFmt = function ($v) {
        if (!is_numeric($v) || (float) $v == 0) return '';
        $n = (float) $v;
        return $n == floor($n) ? (string) (int) $n : rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.');
    };

    // Amount in words (Indian system)
    $words = function (int $n) use (&$words): string {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
                 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($n <= 0)        return '';
        if ($n < 20)        return $ones[$n];
        if ($n < 100)       return trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
        if ($n < 1000)      return trim($ones[intdiv($n, 100)] . ' Hundred ' . $words($n % 100));
        if ($n < 100000)    return trim($words(intdiv($n, 1000)) . ' Thousand ' . $words($n % 1000));
        if ($n < 10000000)  return trim($words(intdiv($n, 100000)) . ' Lakh ' . $words($n % 100000));
        return trim($words(intdiv($n, 10000000)) . ' Crore ' . $words($n % 10000000));
    };

    // ---------- Date ----------
    $date = $inv['date'] ?? '';
    if ($date instanceof \DateTimeInterface) {
        $date = $date->format('d-m-Y');
    } elseif (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}/', $date) && strtotime($date) !== false) {
        $date = date('d-m-Y', strtotime($date));
    }

    // ---------- Totals ----------
    $totalGross = $items->sum(fn ($i) => (float) ($i['gross_wt'] ?? 0));
    $totalNet   = $items->sum(fn ($i) => (float) ($i['net_wt'] ?? 0));
    $totalFine  = $items->sum(fn ($i) => (float) ($i['fine'] ?? 0));
    $totalPcs   = $items->sum(fn ($i) => (float) ($i['pcs'] ?? 0));
    $totalAmt   = $items->sum(fn ($i) => (float) ($i['amount'] ?? 0));

    $closingGold = $inv['closing_gold'] ?? null;
    if (($closingGold === null || $closingGold === '') && $totalFine && empty($inv['opening_gold'])) {
        $closingGold = $fmt($totalFine);
    }
    $closingType = strtoupper($inv['closing_gold_type'] ?? 'Dr') === 'CR' ? 'Cr' : 'Dr';

    // Amount in words: sub total if numeric, otherwise the item amount total
    $wordsBase = is_numeric($inv['sub_total'] ?? null) ? (float) $inv['sub_total'] : $totalAmt;
    $amountWords = '';
    if ($wordsBase > 0) {
        $rupees = (int) floor($wordsBase);
        $paise  = (int) round(($wordsBase - $rupees) * 100);
        $amountWords = 'Rupees ' . ($rupees ? $words($rupees) : 'Zero')
                     . ($paise ? ' and ' . $words($paise) . ' Paise' : '') . ' Only';
    }

    $docTitle = strtoupper($inv['doc_title'] ?? 'VOUCHER');
    $section  = strtoupper($inv['section'] ?? 'SALES');

    // ---------- One-page fitting ----------
    // More items -> tighter rows, so the invoice never spills onto page 2.
    $count   = $items->count();
    $density = $count <= 12 ? 'normal' : ($count <= 20 ? 'compact' : 'tiny');
    $minRows = 12;                                   // short invoices still look like a full form
    $padRows = $density === 'normal' ? max(0, $minRows - $count) : 0;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $invoiceNo }}</title>
    <style>
        /* A4, 8mm margins -> 194mm x 281mm usable area */
        @page { size: A4 portrait; margin: 8mm; }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            color: #000;
            /* DejaVu Sans ships with dompdf and supports Unicode (e.g. the rupee sign) */
            font-family: {!! $isPdf ? '"DejaVu Sans", Arial, sans-serif' : 'Arial, Helvetica, sans-serif' !!};
            font-size: {{ $isPdf ? '10px' : '12px' }};
            line-height: 1.3;
        }

        .sheet { width: 100%; page-break-after: avoid; }

        /* Table-based layout: works in browsers AND dompdf (no flex/grid) */
        table { border-collapse: collapse; border-spacing: 0; width: 100%; }
        td, th { vertical-align: top; word-wrap: break-word; }

        .voucher { border: 1px solid #000; page-break-inside: avoid; }

        /* ---------- Header ---------- */
        .head { border-bottom: 1px solid #000; }
        .head td { padding: 6px 8px; }
        .head .tag {
            display: inline-block;
            border: 1.5px solid #000;
            padding: 2px 10px;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .head .seller {
            font-family: Georgia, "Times New Roman", serif;
            font-weight: bold;
            font-size: 16px;
            margin-left: 8px;
        }
        .head .lbl      { font-weight: normal; color: #444; font-size: 12px; font-family: Arial, Helvetica, sans-serif; }
        .head .customer { font-size: 15px; font-weight: bold; margin-top: 6px; }
        .head .city     { font-size: 12px; margin-top: 2px; color: #222; }
        .head .contact  { text-align: right; vertical-align: bottom; font-size: 12px; font-weight: bold; line-height: 1.6; }

        /* ---------- Meta row ---------- */
        .meta { border-bottom: 1px solid #000; }
        .meta td { padding: 5px 8px; font-size: 12px; }
        .meta b { font-size: 15px; }

        /* ---------- Items ---------- */
        table.items { table-layout: fixed; }
        table.items th,
        table.items td {
            border-left: 1px solid #666;
            padding: 3px 5px;
            font-size: 11.5px;
        }
        table.items th:first-child,
        table.items td:first-child { border-left: 0; }
        table.items tr { page-break-inside: avoid; }

        table.items thead th {
            border-bottom: 1px solid #000;
            background: #f1f1f1;
            font-weight: bold;
            text-align: right;
            padding: 5px 4px;
        }
        table.items thead th { white-space: nowrap; text-transform: uppercase; font-size: 10.5px; letter-spacing: .3px; }
        table.items thead th.l { text-align: left; }
        table.items tbody td { border-bottom: 1px solid #e3e3e3; }
        table.items tr.total-row td, table.items tr.gross-row td { border-bottom: 0; }
        .r { text-align: right; }
        .c { text-align: center; }

        .sec-title  { font-weight: bold; text-decoration: underline; font-size: 12.5px; }
        .item-row td { height: 24px; vertical-align: middle; }
        .item-name  { font-weight: bold; }
        .item-extra { font-weight: normal; font-size: 10.5px; color: #333; }

        .total-row td { border-top: 1px solid #000; font-weight: bold; height: 24px; vertical-align: middle; }
        .gross-row td { border-top: 3px double #000; border-bottom: 1px solid #000; font-weight: bold; height: 26px; vertical-align: middle; background: #f7f7f7; }

        /* Compact / tiny: keeps long invoices on a single page */
        .d-compact table.items td, .d-compact table.items th { font-size: 10.5px; padding: 2px 4px; }
        .d-compact .item-row td { height: 18px; }
        .d-compact .total-row td, .d-compact .gross-row td { height: 21px; }
        .d-compact .head td { padding: 4px 8px; }
        .d-compact .bottom td { font-size: 11px; }

        .d-tiny table.items td, .d-tiny table.items th { font-size: 9.5px; padding: 1px 4px; }
        .d-tiny .item-row td { height: 14px; }
        .d-tiny .total-row td, .d-tiny .gross-row td { height: 18px; }
        .d-tiny .head td { padding: 3px 8px; }
        .d-tiny .head .customer { margin-top: 3px; }
        .d-tiny .meta td { padding: 3px 8px; }
        .d-tiny .bottom td, .d-tiny table.calc td { font-size: 10px; padding-top: 1px; padding-bottom: 1px; }
        .d-tiny table.balances td { font-size: 12px; line-height: 1.4; }
        .d-tiny table.signs td { padding: 6px 8px 14px; }

        /* ---------- Bottom block: remarks (left) + calculation (right) ---------- */
        table.bottom { border-top: 1px solid #000; page-break-inside: avoid; }
        table.bottom > tbody > tr > td { padding: 6px 8px; }
        table.bottom td.left { width: 50%; border-right: 1px solid #000; font-size: 12px; }
        .in-words { font-size: 11px; font-weight: bold; margin-bottom: 8px; }
        .remarks  { font-style: italic; min-height: 40px; }

        table.calc td { padding: 2px 0; font-size: 12.5px; }
        table.calc .k { width: 62%; text-align: right; padding-right: 8px; }
        table.calc .v { width: 38%; font-weight: bold; text-align: right; }
        table.calc tr.rule td { border-top: 1px solid #000; font-size: 14px; font-weight: bold; padding-top: 4px; }

        /* ---------- Balances & signatures ---------- */
        table.balances { border-top: 1px solid #000; page-break-inside: avoid; }
        table.balances td { padding: 5px 8px; font-weight: bold; font-size: 14px; line-height: 1.5; }
        table.balances td.right { width: 50%; }

        table.signs { border-top: 1px solid #000; page-break-inside: avoid; }
        table.signs td { padding: 10px 8px 24px; font-size: 12px; }

        .footer-note {
            text-align: center;
            font-size: 10px;
            font-style: italic;
            color: #444;
            padding-top: 5px;
        }

        /* ---------- Screen-only preview ---------- */
        .toolbar { text-align: center; padding: 10px; }
        .toolbar button {
            padding: 8px 22px;
            font-size: 15px;
            cursor: pointer;
            border: 1px solid #333;
            background: #fff;
            border-radius: 4px;
        }
        .toolbar button:hover { background: #f0f0f0; }

        @media screen {
            body { background: #e9e9e9; }
            .sheet {
                width: 194mm;
                max-width: 100%;
                margin: 10px auto;
                padding: 8mm;
                background: #fff;
                box-sizing: content-box;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .25);
            }
        }

        @media print {
            .toolbar { display: none !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="d-{{ $density }}">

@unless ($isPdf)
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
    </div>
@endunless

<div class="sheet">
    <div class="voucher">

        {{-- Header: [VOUCHER] Seller name / Customer name / city --}}
        <table class="head">
            <tr>
                <td style="width:62%;">
                    <span class="tag">{{ $docTitle }}</span><span class="seller"><span class="lbl">Seller Name :</span> {{ $sellerName }}</span>
                    <div class="customer"><span class="lbl">Customer Name :</span> {{ $customerName }}</div>
                    @if (!empty($inv['city']))
                        <div class="city">{{ $inv['city'] }}</div>
                    @endif
                </td>
                <td class="contact" style="width:38%;">
                    @if ($customerNo !== '')<div>Customer No. : {{ $customerNo }}</div>@endif
                    @if ($sellerNo !== '')<div>Seller No. : {{ $sellerNo }}</div>@endif
                </td>
            </tr>
        </table>

        {{-- Invoice No / Date --}}
        <table class="meta">
            <tr>
                <td style="width:50%;">Invoice No. : <b>{{ $invoiceNo }}</b></td>
                <td style="width:50%; text-align:right;">Date : <b>{{ $date }}</b></td>
            </tr>
        </table>

        {{-- Items (column widths add up to exactly 100%) --}}
        <table class="items">
            <thead>
                <tr>
                    <th class="l" scope="col" style="width:5%;">Sr.</th>
                    <th class="l" scope="col" style="width:28%;">Item Description</th>
                    <th scope="col" style="width:7%;">Pcs</th>
                    <th scope="col" style="width:11%;">G. Wt</th>
                    <th scope="col" style="width:11%;">Net Wt</th>
                    <th scope="col" style="width:8%;">Touch</th>
                    <th scope="col" style="width:7%;">Wst</th>
                    <th scope="col" style="width:11%;">Fine</th>
                    <th scope="col" style="width:12%;">Amt.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td><span class="sec-title">{{ $section }}</span></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>

                @foreach ($items as $i => $item)
                    <tr class="item-row">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="item-name">{{ $item['name'] ?? '' }}</span>
                            @if (!empty($item['extra']))
                                <span class="item-extra">({{ $item['extra'] }})</span>
                            @endif
                        </td>
                        <td class="r">{{ $pcsFmt($item['pcs'] ?? null) }}</td>
                        <td class="r">{{ $fmt($item['gross_wt'] ?? null) }}</td>
                        <td class="r">{{ $fmt($item['net_wt'] ?? null) }}</td>
                        <td class="r">{{ $fmt($item['touch'] ?? null, 2) }}</td>
                        <td class="r">{{ $fmt($item['wastage'] ?? null, 2) }}</td>
                        <td class="r">{{ $fmt($item['fine'] ?? null) }}</td>
                        <td class="r">{{ $inr($item['amount'] ?? null) }}</td>
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
                    <td class="r">{{ $pcsFmt($totalPcs) }}</td>
                    <td class="r">{{ $fmt($totalGross) }}</td>
                    <td class="r">{{ $fmt($totalNet) }}</td>
                    <td></td>
                    <td></td>
                    <td class="r">{{ $fmt($totalFine) }}</td>
                    <td class="r">{{ $totalAmt ? $inr($totalAmt) : '' }}</td>
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
                    <td class="r">{{ $totalAmt ? $inr($totalAmt) : '' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Remarks / amount in words (left) + calculation (right) --}}
        <table class="bottom">
            <tr>
                <td class="left">
                    @if ($amountWords !== '')
                        <div class="in-words">{{ $amountWords }}</div>
                    @endif
                    <div class="remarks">Remarks : {{ $inv['remarks'] ?? '' }}</div>
                </td>
                <td>
                    <table class="calc">
                        <tr><td class="k">Less/Kasar :</td><td class="v">{{ $val($inv['less_kasar'] ?? null, 3) }}</td></tr>
                        <tr><td class="k">Rate Cut Weight :</td><td class="v">{{ $val($inv['rate_cut_weight'] ?? null, 3) }}</td></tr>
                        <tr><td class="k">Rate :</td><td class="v">{{ $val($inv['rate'] ?? null, 2) }}</td></tr>
                        <tr><td class="k">Rate Cut Amt :</td><td class="v">{{ $val($inv['rate_cut_amt'] ?? null, 2) }}</td></tr>
                        <tr><td class="k">Oth. Convert to Fine :</td><td class="v">{{ $val($inv['oth_convert_fine'] ?? null, 3) }}</td></tr>
                        <tr class="rule"><td class="k">Sub Total :</td><td class="v">{{ $val($inv['sub_total'] ?? null, 2) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Balances --}}
        <table class="balances">
            <tr>
                <td>
                    <div>Opening Bal : {{ $val($inv['opening_bal'] ?? null, 2) }}</div>
                    <div>Opening Gold : {{ $val($inv['opening_gold'] ?? null, 3) }}</div>
                </td>
                <td class="right">
                    <div>Closing Bal : {{ $val($inv['closing_bal'] ?? null, 2) }}</div>
                    <div>Closing Gold :
                        @if ($closingGold !== null && $closingGold !== '')
                            {{ $val($closingGold, 3) }} {{ $closingType }}
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