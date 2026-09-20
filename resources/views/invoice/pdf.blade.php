<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>{{ $invoice->invoice_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #ffffff;
        }

        .invoice-container {
            width: 100%;
            padding: 25px;
        }

        /* Header */

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #0891b2;
            margin-bottom: 5px;
        }

        .company-details {
            line-height: 1.6;
            color: #4b5563;
        }

        .invoice-title {
            text-align: right;
            font-size: 30px;
            font-weight: bold;
            color: #111827;
        }

        .invoice-number {
            text-align: right;
            margin-top: 5px;
            font-size: 13px;
            color: #4b5563;
        }

        /* Information */

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .info-box {
            border: 1px solid #e5e7eb;
            padding: 12px;
            vertical-align: top;
            width: 50%;
        }

        .info-title {
            font-weight: bold;
            font-size: 12px;
            color: #0891b2;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .info-content {
            line-height: 1.6;
            color: #374151;
        }

        /* Invoice Meta */

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .meta-table td {
            padding: 7px 10px;
            border: 1px solid #e5e7eb;
        }

        .meta-label {
            font-weight: bold;
            background: #f9fafb;
            width: 25%;
        }

        /* Items */

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background: #0891b2;
            color: #ffffff;
            padding: 9px 7px;
            text-align: left;
            font-size: 11px;
        }

        .items-table td {
            padding: 9px 7px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .items-table tr:nth-child(even) td {
            background: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Summary */

        .summary-wrapper {
            width: 100%;
        }

        .summary-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-label {
            text-align: right;
            color: #4b5563;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
        }

        .grand-total td {
            padding-top: 12px;
            padding-bottom: 12px;
            font-size: 15px;
            font-weight: bold;
            color: #0891b2;
            border-top: 2px solid #0891b2;
            border-bottom: none;
        }

        /* Payment */

        .payment-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-partial {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        /* Notes */

        .notes {
            margin-top: 25px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 7px;
        }

        .notes-content {
            color: #4b5563;
            line-height: 1.5;
        }

        /* Footer */

        .footer {
            margin-top: 35px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .thank-you {
            font-size: 13px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <div class="invoice-container">

        <!-- ===================================================== -->
        <!-- HEADER -->
        <!-- ===================================================== -->

        <table class="header-table">

            <tr>

                <td style="width: 60%; vertical-align: top;">

                    <div class="company-name">
                        Your Company Name
                    </div>

                    <div class="company-details">
                        Your Company Address<br>
                        City, State - PIN Code<br>
                        Phone: +91 XXXXX XXXXX<br>
                        Email: your@email.com<br>
                        GSTIN: XXXXXXXXXXXXXX
                    </div>

                </td>

                <td style="width: 40%; vertical-align: top;">

                    <div class="invoice-title">
                        INVOICE
                    </div>

                    <div class="invoice-number">
                        #{{ $invoice->invoice_number }}
                    </div>

                </td>

            </tr>

        </table>


        <!-- ===================================================== -->
        <!-- CUSTOMER INFORMATION -->
        <!-- ===================================================== -->

        <table class="info-table">

            <tr>

                <td class="info-box">

                    <div class="info-title">
                        Bill To
                    </div>

                    <div class="info-content">

                        <strong>
                            {{ $invoice->customer_name ?? 'N/A' }}
                        </strong>

                        @if (!empty($invoice->address))
                            <br>
                            {{ $invoice->address }}
                        @endif

                        @if (!empty($invoice->phone))
                            <br>
                            Phone: {{ $invoice->phone }}
                        @endif

                        @if (!empty($invoice->email))
                            <br>
                            Email: {{ $invoice->email }}
                        @endif

                    </div>

                </td>


                <td class="info-box">

                    <div class="info-title">
                        Invoice Details
                    </div>

                    <div class="info-content">

                        <strong>
                            Invoice No:
                        </strong>

                        {{ $invoice->invoice_number }}

                        <br>

                        <strong>
                            Invoice Date:
                        </strong>

                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}

                        <br>

                        <strong>
                            Payment Status:
                        </strong>

                        @php
                            $statusClass = match ($invoice->payment_status) {
                                'paid' => 'status-paid',
                                'partial' => 'status-partial',
                                default => 'status-pending',
                            };
                        @endphp

                        <span class="payment-status {{ $statusClass }}">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>

                    </div>

                </td>

            </tr>

        </table>


        <!-- ===================================================== -->
        <!-- ITEMS -->
        <!-- ===================================================== -->

        <table class="items-table">

            <thead>

                <tr>

                    <th style="width: 5%;" class="text-center">
                        #
                    </th>

                    <th style="width: 35%;">
                        Product
                    </th>

                    <th style="width: 12%;" class="text-center">
                        Qty
                    </th>

                    <th style="width: 15%;" class="text-right">
                        Unit Price
                    </th>

                    <th style="width: 13%;" class="text-right">
                        Tax
                    </th>

                    <th style="width: 20%;" class="text-right">
                        Total
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach ($items as $index => $item)

                    <tr>

                        <td class="text-center">
                            {{ $index + 1 }}
                        </td>

                        <td>

                            <strong>
                                {{ $item->product_name }}
                            </strong>

                            @if (!empty($item->product_code))
                                <br>

                                <span style="font-size: 9px; color: #6b7280;">
                                    Code: {{ $item->product_code }}
                                </span>
                            @endif

                            @if (!empty($item->sku))
                                <br>

                                <span style="font-size: 9px; color: #6b7280;">
                                    SKU: {{ $item->sku }}
                                </span>
                            @endif

                        </td>

                        <td class="text-center">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right">
                            ₹{{ number_format($item->unit_price, 2) }}
                        </td>

                        <td class="text-right">
                            ₹{{ number_format($item->tax_amount, 2) }}
                        </td>

                        <td class="text-right">
                            ₹{{ number_format($item->total_amount, 2) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <!-- ===================================================== -->
        <!-- SUMMARY -->
        <!-- ===================================================== -->

        <div class="summary-wrapper">

            <table class="summary-table">

                <tr>

                    <td class="summary-label">
                        Subtotal
                    </td>

                    <td class="summary-value">
                        ₹{{ number_format($invoice->subtotal, 2) }}
                    </td>

                </tr>


                <tr>

                    <td class="summary-label">
                        Total Tax
                    </td>

                    <td class="summary-value">
                        ₹{{ number_format($invoice->total_tax, 2) }}
                    </td>

                </tr>


                <tr>

                    <td class="summary-label">
                        Discount
                    </td>

                    <td class="summary-value">
                        - ₹{{ number_format($invoice->discount_amount, 2) }}
                    </td>

                </tr>


                <tr class="grand-total">

                    <td class="summary-label">
                        Grand Total
                    </td>

                    <td class="summary-value">
                        ₹{{ number_format($invoice->grand_total, 2) }}
                    </td>

                </tr>

            </table>

        </div>


        <!-- ===================================================== -->
        <!-- NOTES -->
        <!-- ===================================================== -->

        @if (!empty($invoice->notes))

            <div class="notes">

                <div class="notes-title">
                    Notes
                </div>

                <div class="notes-content">
                    {!! nl2br(e($invoice->notes)) !!}
                </div>

            </div>

        @endif


        <!-- ===================================================== -->
        <!-- FOOTER -->
        <!-- ===================================================== -->

        <div class="footer">

            <div class="thank-you">
                Thank you for your business!
            </div>

            This is a computer-generated invoice and does not require a signature.

        </div>

    </div>

</body>

</html>