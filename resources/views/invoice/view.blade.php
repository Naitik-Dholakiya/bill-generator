@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Invoice Details'])

            <main class="flex-1 overflow-y-auto p-6">
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                        <p class="font-semibold">Please review the payment details:</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Invoice {{ $invoice->invoice_number }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            View invoice information and preview the generated invoice.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('invoice.edit', $invoice->invoice_id) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 font-medium text-white transition hover:bg-amber-600">
                            <i class="ti ti-edit"></i>
                            Edit
                        </a>

                        <form action="{{ route('invoice.delete', $invoice->invoice_id) }}" method="POST"
                            onsubmit="return confirm('Move this invoice to trash?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-5 py-2.5 font-medium text-white transition hover:bg-red-600">
                                <i class="ti ti-trash"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Customer</p>
                        <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $invoice->customer_name ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Invoice Date</p>
                        <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Status</p>
                        <p class="mt-1 text-lg font-bold capitalize text-gray-900 dark:text-white">{{ $invoice->payment_status }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Grand Total</p>
                        <p class="mt-1 text-lg font-bold text-cyan-600">₹{{ number_format($invoice->grand_total, 2) }}</p>
                    </div>
                </div>

                <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Items</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Product</th>
                                    @if ($isJewelryUser)
                                        <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Touch</th>
                                    @endif
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Quantity</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Unit Price</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Tax</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Discount</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @forelse ($items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $item->product_name ?? 'Product unavailable' }}</td>
                                        @if ($isJewelryUser)
                                            <td class="px-6 py-4 text-right">{{ $item->touch !== null ? number_format((float) $item->touch, 3) : '-' }}</td>
                                        @endif
                                        <td class="px-6 py-4 text-right">{{ number_format($item->quantity, 3) }}</td>
                                        <td class="px-6 py-4 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right">₹{{ number_format($item->tax_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right">₹{{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-semibold">₹{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isJewelryUser ? 7 : 6 }}" class="px-6 py-8 text-center text-gray-500">No active invoice items.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                        <div class="space-y-2 text-right">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal: ₹{{ number_format($invoice->subtotal, 2) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tax: ₹{{ number_format($invoice->total_tax, 2) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Discount: ₹{{ number_format($invoice->discount_amount, 2) }}</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">Total: ₹{{ number_format($invoice->grand_total, 2) }}</p>
                        </div>
                    </div>
                </div>

                @unless ($isJewelryUser)
                    @php
                        $recordedPayments = $payments->whereNull('voided_at');
                        $paidAmount = ($invoice->opening_paid_amount ?? 0) + $recordedPayments->sum('amount');
                        $balanceDue = max((float) $invoice->grand_total - (float) $paidAmount, 0);
                    @endphp
                    <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payments</h2>
                            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                @if ($invoice->opening_paid_amount === null)
                                    <span>Previously paid: <strong>Needs reconciliation</strong></span>
                                    <span>Balance due: <strong>Unknown until reconciled</strong></span>
                                @else
                                    <span>Paid: <strong>₹{{ number_format((float) $paidAmount, 2) }}</strong></span>
                                    <span>Balance due: <strong>₹{{ number_format($balanceDue, 2) }}</strong></span>
                                @endif
                            </div>
                        </div>

                        @if ($invoice->opening_paid_amount === null && $invoice->payment_status === 'partial')
                            <div class="mx-6 mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                                This invoice was marked partial before payment amounts were tracked. Enter the amount previously received when recording the next payment; no historical amount has been assumed.
                            </div>
                        @endif

                        @if ((float) $invoice->grand_total > 0 && ($balanceDue > 0 || $invoice->opening_paid_amount === null))
                            <form action="{{ route('invoice.payments.store', $invoice->invoice_id) }}" method="POST" class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 xl:grid-cols-4">
                                @csrf
                                @if ($invoice->opening_paid_amount === null)
                                    <div>
                                        <label for="opening-paid-amount" class="mb-1 block text-sm font-medium">Previously received</label>
                                        <input id="opening-paid-amount" type="number" name="opening_paid_amount" min="0" max="{{ $invoice->grand_total }}" step="0.01" required value="{{ old('opening_paid_amount') }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                        @error('opening_paid_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                                <div>
                                    <label for="payment-amount" class="mb-1 block text-sm font-medium">New payment amount</label>
                                    <input id="payment-amount" type="number" name="amount" min="0.01" max="{{ $balanceDue }}" step="0.01" required value="{{ old('amount') }}" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                    @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="payment-date" class="mb-1 block text-sm font-medium">Received on</label>
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
                                                        <form action="{{ route('invoice.payments.void', [$invoice->invoice_id, $payment->invoice_payment_id]) }}" method="POST" class="flex items-center gap-2">
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

                @include('invoice.revisions')

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Preview</h2>
                        <a href="{{ route('invoice.pdf', $invoice->invoice_id) }}" target="_blank"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 font-medium text-white hover:bg-cyan-700">
                            <i class="ti ti-external-link"></i>
                            Open PDF
                        </a>
                    </div>
                    <iframe
                        src="{{ route('invoice.pdf', $invoice->invoice_id) }}"
                        title="Invoice PDF preview"
                        class="h-[80vh] w-full"
                        loading="lazy">
                    </iframe>
                </div>
            </main>

            @include('layout.footer')
        </div>
    </div>
@endsection
