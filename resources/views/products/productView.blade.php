@extends('layouts.app')

@section('title', 'Product Details')

@section('content')

<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        @include('layout.navbar', ['title' => 'Product Details'])

        <main class="flex-1 overflow-y-auto p-6">

            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $product->product_name }}
                    </h1>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        View complete product information.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">

                    <!-- Edit -->
                    <a href="{{ route('products.edit', $product->product_id) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium transition">

                        <i class="ti ti-edit"></i>
                        Edit
                    </a>

                    <!-- Delete -->
                    <form action="{{ route('products.delete', $product->product_id) }}"
                        method="POST"
                        onsubmit="return confirm('Move this product to trash?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-medium transition">

                            <i class="ti ti-trash"></i>
                            Delete

                        </button>

                    </form>

                </div>

            </div>

            <!-- Product Profile -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 mb-6">

                <div class="flex items-center gap-4">

                    <div
                        class="w-20 h-20 rounded-full bg-cyan-100 dark:bg-cyan-500/10 flex items-center justify-center">

                        <i class="ti ti-package text-cyan-600 text-4xl"></i>

                    </div>

                    <div>

                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $product->product_name }}
                        </h2>

                        <p class="text-gray-500 dark:text-gray-400">
                            Product Code : {{ $product->product_code }}
                        </p>

                    </div>

                </div>

            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Product Code
                    </p>

                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                        {{ $product->product_code }}
                    </p>

                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Purchase Price
                    </p>

                    <p class="text-lg font-bold text-green-600 mt-1">
                        ₹{{ number_format($product->purchase_price,2) }}
                    </p>

                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Selling Price
                    </p>

                    <p class="text-lg font-bold text-blue-600 mt-1">
                        ₹{{ number_format($product->selling_price,2) }}
                    </p>

                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status
                    </p>

                    @if($product->status)
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400 text-sm font-semibold mt-2">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 text-sm font-semibold mt-2">
                            Inactive
                        </span>
                    @endif

                </div>

            </div>

            <!-- Product Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Product Information -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Product Information
                    </h3>

                    <div class="space-y-5">

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Product Name</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $product->product_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Product Code</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $product->product_code }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Category</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $product->category->category_name ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Supplier</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $product->supplier->supplier_name ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Purchase Price</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                ₹{{ number_format($product->purchase_price,2) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Selling Price</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                ₹{{ number_format($product->selling_price,2) }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- Inventory -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Inventory Information
                    </h3>

                    <div class="space-y-5">

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Reorder Level
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $product->reorder_level }}
                            </p>

                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Profit Margin
                            </p>

                            <p class="font-medium text-green-600">
                                ₹{{ number_format($product->selling_price - $product->purchase_price,2) }}
                            </p>

                        </div>

                        <div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Status
                            </p>

                            @if($product->status)

                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400 text-sm font-semibold">
                                    Active
                                </span>

                            @else

                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 text-sm font-semibold">
                                    Inactive
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

            <!-- System Information -->
            <div class="mt-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                    System Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Created By
                        </p>

                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $product->creator->user_name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Updated By
                        </p>

                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $product->updater->user_name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Created At
                        </p>

                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $product->created_at ? $product->created_at->format('d M Y h:i A') : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Last Updated
                        </p>

                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $product->updated_at ? $product->updated_at->format('d M Y h:i A') : '-' }}
                        </p>
                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

@endsection