@extends('layouts.app')

@section('title', 'Stock')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')

        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Stock'])

            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Review and update the available quantity for each product.</p>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <form method="GET" action="{{ route('stock.index') }}">
                        <label for="stock-search" class="sr-only">Search products</label>
                        <input id="stock-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by product code, name, or category..."
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:ring-2 focus:ring-cyan-500 dark:border-zinc-700 dark:bg-zinc-800">
                    </form>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Product</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Category</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Available</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Reorder Level</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Last Updated</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold">Update Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                                @forelse ($products as $product)
                                    @php($quantity = $product->inventory?->quantity_in_stock ?? 0)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-6 py-4">
                                            <span class="block font-medium text-gray-900 dark:text-white">{{ $product->product_name }}</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $product->product_code }}</span>
                                        </td>
                                        <td class="px-6 py-4">{{ $product->category?->category_name ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold {{ $quantity <= $product->reorder_level ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">
                                                {{ number_format($quantity) }}
                                            </span>
                                            @if ($quantity <= $product->reorder_level)
                                                <span class="ml-2 text-xs font-medium text-amber-600 dark:text-amber-400">Reorder</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ number_format($product->reorder_level) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $product->inventory?->updated_at?->format('d M Y, h:i A') ?? 'Not recorded' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <form action="{{ route('stock.update', ['productId' => $product->product_id, 'search' => request('search')]) }}"
                                                method="POST" class="flex items-start justify-end gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="_stock_product_id" value="{{ $product->product_id }}">
                                                <div>
                                                    <label class="sr-only" for="quantity-{{ $product->product_id }}">Quantity for {{ $product->product_name }}</label>
                                                    <input id="quantity-{{ $product->product_id }}" type="number" name="quantity_in_stock"
                                                        value="{{ old('_stock_product_id') == $product->product_id ? old('quantity_in_stock', $quantity) : $quantity }}" min="0" max="2147483647" required
                                                        class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800">
                                                    @if ($errors->has('quantity_in_stock') && old('_stock_product_id') == $product->product_id)
                                                        <p class="mt-1 text-xs text-red-600">{{ $errors->first('quantity_in_stock') }}</p>
                                                    @endif
                                                </div>
                                                <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                                                    Save
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            No products found. Add products before managing stock.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($products->hasPages())
                        <div class="border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </main>

            @include('layout.footer')
        </div>
    </div>
@endsection
