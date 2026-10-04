@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Invoice Details'])

            <main class="flex-1 overflow-y-auto p-6">
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
                                        <td class="px-6 py-4 text-right">{{ number_format($item->quantity, 3) }}</td>
                                        <td class="px-6 py-4 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right">₹{{ number_format($item->tax_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right">₹{{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-semibold">₹{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No active invoice items.</td>
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
        </div>
    </div>
@endsection
