@extends('layouts.app')

@section('title', 'Stock History')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Stock History'])
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <a href="{{ route('stock.index') }}" class="text-sm font-medium text-cyan-700 hover:underline dark:text-cyan-300">← Stock</a>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $product->product_name }} · Stock History</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $product->product_code }} · Current on hand: {{ number_format((int) ($product->inventory?->quantity_in_stock ?? 0)) }}</p>
                    </div>
                </div>

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-zinc-800/60 dark:text-gray-400">
                                <tr>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Movement</th>
                                    <th class="px-5 py-3">Reference</th>
                                    <th class="px-5 py-3 text-right">Change</th>
                                    <th class="px-5 py-3 text-right">On hand after</th>
                                    <th class="px-5 py-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @forelse ($movements as $movement)
                                    <tr>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($movement->created_at)->format('d M Y, h:i A') }}</td>
                                        <td class="px-5 py-4 capitalize">{{ str_replace('_', ' ', $movement->movement_type) }}</td>
                                        <td class="px-5 py-4 capitalize">
                                            {{ $movement->reference_type ? str_replace('_', ' ', $movement->reference_type) : '—' }}
                                            @if ($movement->reference_id)<span class="text-gray-500">#{{ $movement->reference_id }}</span>@endif
                                        </td>
                                        <td class="px-5 py-4 text-right font-semibold tabular-nums {{ $movement->quantity_delta < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                            {{ $movement->quantity_delta > 0 ? '+' : '' }}{{ number_format($movement->quantity_delta) }}
                                        </td>
                                        <td class="px-5 py-4 text-right tabular-nums {{ $movement->quantity_after < 0 ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">{{ number_format($movement->quantity_after) }}</td>
                                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $movement->notes ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">No stock movements have been recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($movements->hasPages())
                        <div class="border-t border-gray-200 px-5 py-4 dark:border-zinc-800">{{ $movements->links() }}</div>
                    @endif
                </section>
            </main>
            @include('layout.footer')
        </div>
    </div>
@endsection
