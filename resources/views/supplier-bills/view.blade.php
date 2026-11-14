@extends('layouts.app')

@section('title', 'Supplier Bill Details')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Supplier Bill Details'])
            <main class="flex-1 overflow-y-auto p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                        <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">{{ session('success') }}</div>
                @endif
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Supplier Bill {{ $bill->purchase_number }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Purchase details, payment status, and items received.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('supplier-bills.pdf', $bill->purchase_id) }}" target="_blank" class="rounded-xl border border-gray-300 px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800"><i class="ti ti-file-type-pdf mr-1"></i>PDF</a>
                        <a href="{{ route('supplier-bills.edit', $bill->purchase_id) }}" class="rounded-xl bg-amber-500 px-4 py-2.5 font-medium text-white hover:bg-amber-600"><i class="ti ti-edit mr-1"></i>Edit</a>
                        <form action="{{ route('supplier-bills.destroy', $bill->purchase_id) }}" method="POST" onsubmit="return confirm('Delete this bill and reverse its stock quantities?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 font-medium text-white hover:bg-red-700"><i class="ti ti-trash mr-1"></i>Delete</button>
                        </form>
                    </div>
                </div>
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        'Supplier' => $bill->supplier_name,
                        'Bill Date' => \Carbon\Carbon::parse($bill->purchase_date)->format('d M Y'),
                        'Payment Status' => ucfirst($bill->payment_status),
                        ($isJewelryUser ? 'Fine Weight' : 'Bill Total') => $isJewelryUser
                            ? number_format((float) $items->sum('fine_weight'), 3).' g'
                            : '₹'.number_format($bill->grand_total, 2),
                    ] as $label => $value)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
                @unless ($isJewelryUser)
                    @php
                        $recordedPayments = $payments->whereNull('voided_at');
                        $paidAmount = ($bill->opening_paid_amount ?? 0) + $recordedPayments->sum('amount');
                        $balanceDue = max((float) $bill->grand_total - (float) $paidAmount, 0);
                    @endphp
                    <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payments</h2>
                            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                @if ($bill->opening_paid_amount === null)
                                    <span>Previously paid: <strong>Needs reconciliation</strong></span>
                                    <span>Balance due: <strong>Unknown until reconciled</strong></span>
                                @else
                                    <span>Paid: <strong>₹{{ number_format((float) $paidAmount, 2) }}</strong></span>
                                    <span>Balance due: <strong>₹{{ number_format($balanceDue, 2) }}</strong></span>
                                @endif
                            </div>
                        </div>
                        @if ($bill->opening_paid_amount === null && $bill->payment_status === 'partial')
                            <div class="mx-6 mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                                This bill was marked partial before payment amounts were tracked. Enter the amount previously paid when recording the next payment; no historical amount has been assumed.
                            </div>
                        @endif
                        @if ((float) $bill->grand_total > 0 && ($balanceDue > 0 || $bill->opening_paid_amount === null))
                            <form action="{{ route('supplier-bills.payments.store', $bill->purchase_id) }}" method="POST" class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 xl:grid-cols-4">
                                @csrf
                                @if ($bill->opening_paid_amount === null)
                                    <div>
                                        <label for="opening-paid-amount" class="mb-1 block text-sm font-medium">Previously paid</label>
                                        <input id="opening-paid-amount" type="number" name="opening_paid_amount" min="0" max="{{ $bill->grand_total }}" step="0.01" required value="{{ old('opening_paid_amount') }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                        @error('opening_paid_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                                <div>
                                    <label for="payment-amount" class="mb-1 block text-sm font-medium">New payment amount</label>
                                    <input id="payment-amount" type="number" name="amount" min="0.01" max="{{ $balanceDue }}" step="0.01" required value="{{ old('amount') }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                    @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="payment-date" class="mb-1 block text-sm font-medium">Paid on</label>
                                    <input id="payment-date" type="date" name="paid_at" required value="{{ old('paid_at', now()->toDateString()) }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                    @error('paid_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="payment-method" class="mb-1 block text-sm font-medium">Method</label>
                                    <select id="payment-method" name="method" required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                        @foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'card' => 'Card', 'upi' => 'UPI', 'cheque' => 'Cheque', 'other' => 'Other'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('method', 'cash') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="payment-reference" class="mb-1 block text-sm font-medium">Reference (optional)</label>
                                    <input id="payment-reference" name="reference_number" maxlength="100" value="{{ old('reference_number') }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                </div>
                                <div class="sm:col-span-2 xl:col-span-3">
                                    <label for="payment-notes" class="mb-1 block text-sm font-medium">Notes (optional)</label>
                                    <input id="payment-notes" name="notes" maxlength="2000" value="{{ old('notes') }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="w-full rounded-lg bg-cyan-600 px-4 py-2 font-semibold text-white hover:bg-cyan-700">Record payment</button>
                                </div>
                            </form>
                        @endif
                        @if ($payments->isNotEmpty())
                            <div class="overflow-x-auto border-t border-gray-200 dark:border-zinc-800">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 text-left dark:bg-zinc-800/60">
                                        <tr>
                                            <th class="px-6 py-3">Date</th>
                                            <th class="px-6 py-3">Method / reference</th>
                                            <th class="px-6 py-3 text-right">Amount</th>
                                            <th class="px-6 py-3">Status / action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                        @foreach ($payments as $payment)
                                            <tr>
                                                <td class="px-6 py-3">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y') }}</td>
                                                <td class="px-6 py-3">
                                                    <span class="capitalize">{{ str_replace('_', ' ', $payment->method) }}</span>
                                                    @if ($payment->reference_number)<span class="block text-xs text-gray-500">{{ $payment->reference_number }}</span>@endif
                                                    @if ($payment->void_reason)<span class="block text-xs text-red-600">Void reason: {{ $payment->void_reason }}</span>@endif
                                                </td>
                                                <td class="px-6 py-3 text-right">₹{{ number_format((float) $payment->amount, 2) }}</td>
                                                <td class="px-6 py-3">
                                                    @if ($payment->voided_at)
                                                        <span class="text-red-600">Voided</span>
                                                    @else
                                                        <form action="{{ route('supplier-bills.payments.void', [$bill->purchase_id, $payment->supplier_bill_payment_id]) }}" method="POST" class="flex items-center gap-2">
                                                            @csrf
                                                            <input name="void_reason" required maxlength="500" placeholder="Reason to void" class="min-w-0 rounded border border-gray-200 px-2 py-1 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Void</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>
                @endunless
                @if ($bill->notes)
                    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Notes</h2>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $bill->notes }}</p>
                    </div>
                @endif
                @if ($isJewelryUser)
                    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Voucher Section</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $bill->voucher_section ?? 'PURCHASE' }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Less / kasar</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $bill->less_kasar !== null ? number_format((float) $bill->less_kasar, 3).' g' : '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Rate cut / other converted fine</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $bill->rate_cut_weight !== null ? number_format((float) $bill->rate_cut_weight, 3).' g' : '—' }} / {{ $bill->other_convert_fine !== null ? number_format((float) $bill->other_convert_fine, 3).' g' : '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Opening gold · Credit</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $bill->opening_gold !== null ? number_format((float) $bill->opening_gold, 3).' g' : '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Closing gold · Debit</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $bill->closing_gold !== null ? number_format((float) $bill->closing_gold, 3).' g' : '—' }}</p>
                        </div>
                    </div>
                    @if ($bill->footer_note)
                        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">{{ $bill->footer_note }}</div>
                    @endif
                @endif
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800"><h2 class="text-lg font-semibold">{{ $isJewelryUser ? 'Jewellery Items' : 'Purchased Items' }}</h2></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/70">
                                @if ($isJewelryUser)
                                    <tr><th class="px-5 py-3 text-left font-semibold">Product</th><th class="px-5 py-3 text-right font-semibold">Pcs</th><th class="px-5 py-3 text-right font-semibold">Gross wt (g)</th><th class="px-5 py-3 text-right font-semibold">Net wt (g)</th><th class="px-5 py-3 text-right font-semibold">Touch</th><th class="px-5 py-3 text-right font-semibold">Wastage</th><th class="px-5 py-3 text-right font-semibold">Fine wt (g)</th></tr>
                                @else
                                    <tr><th class="px-5 py-3 text-left font-semibold">Product</th><th class="px-5 py-3 text-right font-semibold">Quantity</th><th class="px-5 py-3 text-right font-semibold">Unit Cost</th><th class="px-5 py-3 text-right font-semibold">Tax</th><th class="px-5 py-3 text-right font-semibold">Discount</th><th class="px-5 py-3 text-right font-semibold">Line Total</th></tr>
                                @endif
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @foreach ($items as $item)
                                    <tr>
                                        <td class="px-5 py-4"><span class="font-medium">{{ $item->product_name }}</span><span class="mt-1 block text-xs text-gray-500">{{ $item->product_code }}</span></td>
                                        @if ($isJewelryUser)
                                            <td class="px-5 py-4 text-right">{{ $item->pcs ?? $item->quantity }}</td>
                                            <td class="px-5 py-4 text-right">{{ $item->gross_weight !== null ? number_format((float) $item->gross_weight, 3) : '—' }}</td>
                                            <td class="px-5 py-4 text-right">{{ $item->net_weight !== null ? number_format((float) $item->net_weight, 3) : '—' }}</td>
                                            <td class="px-5 py-4 text-right">{{ $item->touch !== null ? number_format((float) $item->touch, 3).'%' : '—' }}</td>
                                            <td class="px-5 py-4 text-right">{{ $item->wastage !== null ? number_format((float) $item->wastage, 3).'%' : '—' }}</td>
                                            <td class="px-5 py-4 text-right font-semibold text-amber-700 dark:text-amber-300">{{ number_format((float) $item->fine_weight, 3) }}</td>
                                        @else
                                            <td class="px-5 py-4 text-right">{{ number_format($item->quantity) }}</td>
                                            <td class="px-5 py-4 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-5 py-4 text-right">₹{{ number_format($item->tax_amount, 2) }} <span class="text-xs text-gray-500">({{ number_format($item->tax_percent, 2) }}%)</span></td>
                                            <td class="px-5 py-4 text-right">₹{{ number_format($item->discount_amount, 2) }}</td>
                                            <td class="px-5 py-4 text-right font-semibold">₹{{ number_format($item->line_total, 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                        <div class="space-y-2 text-right">
                            @if ($isJewelryUser)
                                <p class="text-lg font-bold text-amber-700 dark:text-amber-300">Total Fine: {{ number_format((float) $items->sum('fine_weight'), 3) }} g</p>
                            @else
                                <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal: ₹{{ number_format($bill->subtotal, 2) }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Tax: ₹{{ number_format($bill->total_tax, 2) }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Discount: ₹{{ number_format($bill->discount_amount, 2) }}</p>
                                <p class="text-lg font-bold">Total: ₹{{ number_format($bill->grand_total, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
            @include('layout.footer')
        </div>
    </div>
@endsection
