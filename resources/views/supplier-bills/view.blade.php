@extends('layouts.app')

@section('title', 'Supplier Bill Details')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Supplier Bill Details'])
            <main class="flex-1 overflow-y-auto p-6">
                @if ($errors->has('stock'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ $errors->first('stock') }}</div>
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
                        'Bill Total' => '₹'.number_format($bill->grand_total, 2),
                    ] as $label => $value)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
                @if ($bill->notes)
                    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Notes</h2>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $bill->notes }}</p>
                    </div>
                @endif
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800"><h2 class="text-lg font-semibold">Purchased Items</h2></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/70">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold">Product</th>
                                    <th class="px-5 py-3 text-right font-semibold">Quantity</th>
                                    <th class="px-5 py-3 text-right font-semibold">Unit Cost</th>
                                    <th class="px-5 py-3 text-right font-semibold">Tax</th>
                                    <th class="px-5 py-3 text-right font-semibold">Discount</th>
                                    <th class="px-5 py-3 text-right font-semibold">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @foreach ($items as $item)
                                    <tr>
                                        <td class="px-5 py-4"><span class="font-medium">{{ $item->product_name }}</span><span class="mt-1 block text-xs text-gray-500">{{ $item->product_code }}</span></td>
                                        <td class="px-5 py-4 text-right">{{ number_format($item->quantity) }}</td>
                                        <td class="px-5 py-4 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-5 py-4 text-right">₹{{ number_format($item->tax_amount, 2) }} <span class="text-xs text-gray-500">({{ number_format($item->tax_percent, 2) }}%)</span></td>
                                        <td class="px-5 py-4 text-right">₹{{ number_format($item->discount_amount, 2) }}</td>
                                        <td class="px-5 py-4 text-right font-semibold">₹{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                        <div class="space-y-2 text-right">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal: ₹{{ number_format($bill->subtotal, 2) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tax: ₹{{ number_format($bill->total_tax, 2) }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Discount: ₹{{ number_format($bill->discount_amount, 2) }}</p>
                            <p class="text-lg font-bold">Total: ₹{{ number_format($bill->grand_total, 2) }}</p>
                        </div>
                    </div>
                </div>
            </main>
            @include('layout.footer')
        </div>
    </div>
@endsection
