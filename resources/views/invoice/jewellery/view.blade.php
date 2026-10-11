@extends('layouts.app')

@section('title', 'Jewellery Voucher')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Jewellery Voucher'])
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('invoice.index') }}" class="text-sm font-medium text-amber-700 dark:text-amber-300">← All vouchers</a>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->customer_name ?? 'Customer unavailable' }} · {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('invoice.pdf', ['invoice' => $invoice->invoice_id, 'download' => 1]) }}" class="rounded-xl border border-amber-200 px-4 py-2.5 font-medium text-amber-800 hover:bg-amber-50 dark:border-amber-900/50 dark:text-amber-300 dark:hover:bg-amber-950/30">Download invoice</a>
                        <a href="{{ route('invoice.edit', $invoice->invoice_id) }}" class="rounded-xl bg-amber-600 px-4 py-2.5 font-medium text-white hover:bg-amber-700">Edit voucher</a>
                        <form action="{{ route('invoice.delete', $invoice->invoice_id) }}" method="POST" onsubmit="return confirm('Move this voucher to trash?')">
                            @csrf @method('DELETE')
                            <button class="rounded-xl border border-red-200 px-4 py-2.5 font-medium text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:hover:bg-red-950/30">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Party</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $invoice->customer_name ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Voucher section</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $invoice->voucher_section ?? 'SALES' }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/30">
                        <p class="text-sm text-amber-800 dark:text-amber-200">Total fine</p>
                        <p class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200">{{ number_format((float) $rows->sum('fine_weight'), 3) }} <span class="text-base font-semibold">g</span></p>
                    </div>
                </div>

                <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Item details</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $rows->count() }} {{ \Illuminate\Support\Str::plural('item', $rows->count()) }} · all weights in grams</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-zinc-800/60 dark:text-gray-400">
                                <tr><th class="px-5 py-3">Product</th><th class="px-5 py-3 text-right">Pcs</th><th class="px-5 py-3 text-right">Gross wt (g)</th><th class="px-5 py-3 text-right">Net wt (g)</th><th class="px-5 py-3 text-right">Touch</th><th class="px-5 py-3 text-right">Wastage</th><th class="px-5 py-3 text-right">Fine wt (g)</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @forelse ($rows as $item)
                                    <tr class="transition hover:bg-amber-50/50 dark:hover:bg-amber-950/10">
                                        <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">{{ $item->product_name ?? 'Product unavailable' }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ $item->pcs ?? $item->quantity }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ $item->gross_weight !== null ? number_format((float) $item->gross_weight, 3) : '—' }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ $item->net_weight !== null ? number_format((float) $item->net_weight, 3) : '—' }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ $item->touch === null ? '—' : number_format((float) $item->touch, 3) . '%' }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ $item->wastage === null ? '—' : number_format((float) $item->wastage, 3) . '%' }}</td>
                                        <td class="px-5 py-4 text-right font-bold tabular-nums text-amber-700 dark:text-amber-300">{{ number_format((float) $item->fine_weight, 3) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">No items.</td></tr>
                                @endforelse
                            </tbody>
                            @if ($rows->isNotEmpty())
                                <tfoot class="border-t-2 border-gray-200 bg-gray-50 font-semibold text-gray-900 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-white">
                                    <tr>
                                        <td class="px-5 py-4">Total</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ $rows->sum('pcs') }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ number_format((float) $rows->sum('gross_weight'), 3) }}</td>
                                        <td class="px-5 py-4 text-right tabular-nums">{{ number_format((float) $rows->sum('net_weight'), 3) }}</td>
                                        <td colspan="2"></td>
                                        <td class="px-5 py-4 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ number_format((float) $rows->sum('fine_weight'), 3) }} g</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    <div class="grid grid-cols-1 gap-5 border-t border-gray-200 p-6 text-sm dark:border-zinc-800 md:grid-cols-2">
                        <div class="space-y-2 text-gray-600 dark:text-gray-300">
                            <p>Less / kasar: {{ $invoice->less_kasar !== null ? number_format((float) $invoice->less_kasar, 3) . ' g' : '—' }}</p>
                            <p>Rate cut weight: {{ $invoice->rate_cut_weight !== null ? number_format((float) $invoice->rate_cut_weight, 3) . ' g' : '—' }}</p>
                            <p>Other converted fine: {{ $invoice->other_convert_fine !== null ? number_format((float) $invoice->other_convert_fine, 3) . ' g' : '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-4 text-right dark:bg-amber-950/30">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-800 dark:text-amber-200">Net fine</p>
                            <p class="mt-1 text-xl font-bold text-amber-800 dark:text-amber-200">{{ number_format((float) $rows->sum('fine_weight'), 3) }} g</p>
                        </div>
                    </div>
                </section>

                <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Party gold balance</h2>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Opening gold: {{ $invoice->opening_gold !== null ? number_format((float) $invoice->opening_gold, 3) . ' g Cr' : '—' }}</p>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Closing gold: {{ $invoice->closing_gold !== null ? number_format((float) $invoice->closing_gold, 3) . ' g Dr' : '—' }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Remarks</h2>
                        <p class="mt-3 whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-300">{{ $invoice->notes ?: '—' }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Invoice</h2>
                    </div>
                    <iframe
                        src="{{ route('invoice.pdf', $invoice->invoice_id) }}"
                        title="Jewellery invoice"
                        class="block h-[80vh] w-full border-0">
                    </iframe>
                </section>

            </main>
        </div>
    </div>
@endsection
