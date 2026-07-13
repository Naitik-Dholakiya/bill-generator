@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        @include('layout.navbar', ['title' => 'Edit Product'])

        <main class="flex-1 overflow-y-auto p-6">

            <!-- Page Header -->
            <div class="mb-6">

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit Product
                </h1>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Update product information.
                </p>

            </div>

            <form action="{{ route('editProductPost', $product->product_id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">

                    <!-- Card Header -->
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Product Information
                        </h2>

                    </div>

                    <!-- Body -->
                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Product Code -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Product Code
                                </label>

                                <input type="text"
                                    name="lf_product_code"
                                    value="{{ $product->product_code }}"
                                    readonly
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-gray-100 dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    cursor-not-allowed">

                            </div>

                            <!-- Product Name -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Product Name <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="tb_product_name"
                                    value="{{ old('tb_product_name',$product->product_name) }}"
                                    required
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                @error('tb_product_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Category -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Category <span class="text-red-500">*</span>
                                </label>

                                <select name="tb_category"
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                    <option value="">Select Category</option>

                                    @foreach($categories as $category)

                                        <option value="{{ $category->category_id }}"
                                            {{ old('tb_category',$product->category_id)==$category->category_id ? 'selected' : '' }}>

                                            {{ $category->category_name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('tb_category')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Supplier -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Supplier <span class="text-red-500">*</span>
                                </label>

                                <select name="tb_supplier"
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                    <option value="">Select Supplier</option>

                                    @foreach($suppliers as $supplier)

                                        <option value="{{ $supplier->supplier_id }}"
                                            {{ old('tb_supplier',$product->supplier_id)==$supplier->supplier_id ? 'selected' : '' }}>

                                            {{ $supplier->supplier_name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('tb_supplier')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Purchase Price -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Purchase Price <span class="text-red-500">*</span>
                                </label>

                                <input type="number"
                                    name="tb_purchase_price"
                                    step="0.01"
                                    value="{{ old('tb_purchase_price',$product->purchase_price) }}"
                                    required
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                @error('tb_purchase_price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Selling Price -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Selling Price <span class="text-red-500">*</span>
                                </label>

                                <input type="number"
                                    name="tb_selling_price"
                                    step="0.01"
                                    value="{{ old('tb_selling_price',$product->selling_price) }}"
                                    required
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                @error('tb_selling_price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Reorder Level -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reorder Level
                                </label>

                                <input type="number"
                                    name="tb_reorder_level"
                                    value="{{ old('tb_reorder_level',$product->reorder_level) }}"
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                @error('tb_reorder_level')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Status -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>

                                <select name="tb_status"
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500 outline-none">

                                    <option value="1"
                                        {{ old('tb_status',$product->status)==1 ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="0"
                                        {{ old('tb_status',$product->status)==0 ? 'selected' : '' }}>
                                        Inactive
                                    </option>

                                </select>

                                @error('tb_status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800">

                        <div class="flex justify-end gap-3">

                            <a href="{{ route('products.index') }}"
                                class="px-5 py-2.5 rounded-xl
                                border border-gray-300 dark:border-zinc-700
                                text-gray-700 dark:text-gray-300
                                hover:bg-gray-100 dark:hover:bg-zinc-800">

                                Cancel

                            </a>

                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl
                                bg-cyan-600 hover:bg-cyan-700
                                text-white font-medium">

                                <i class="ti ti-device-floppy mr-1"></i>
                                Update Product

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </main>

    </div>

</div>

@endsection