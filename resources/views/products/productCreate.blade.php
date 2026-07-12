@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        @include('layout.navbar',['title'=>'Add Product'])

        <main class="flex-1 overflow-y-auto p-6">

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Add Product
                </h1>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Create a new product for inventory management.
                </p>
            </div>

            <form action="{{ route('createProductPost') }}" method="POST">
                @csrf

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Product Information
                        </h2>
                    </div>

                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Product Code -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Product Code
                                </label>

                                <input type="text"
                                    name="lf_product_code"
                                    value="{{ $productCode ?? 'Auto Generated' }}"
                                    readonly
                                    class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 cursor-not-allowed">
                            </div>

                            <!-- Product Name -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Product Name <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="tb_product_name"
                                    value="{{ old('tb_product_name') }}"
                                    required
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                @error('tb_product_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Internal Code -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Internal Code
                                </label>

                                <input type="text"
                                    name="tb_internal_code"
                                    value="{{ old('tb_internal_code') }}"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                            </div>

                            <!-- Barcode -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Barcode
                                </label>

                                <input type="text"
                                    name="tb_barcode"
                                    value="{{ old('tb_barcode') }}"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                            </div>

                            <!-- Supplier -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Supplier <span class="text-red-500">*</span>
                                </label>

                                <select name="dd_supplier"
                                    required
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">

                                    <option value="">Select Supplier</option>

                                    @foreach($suppliers as $supplier)

                                        <option value="{{ $supplier->supplier_id }}"
                                            {{ old('dd_supplier')==$supplier->supplier_id ? 'selected' : '' }}>

                                            {{ $supplier->supplier_name }}

                                        </option>

                                    @endforeach

                                </select>
                            </div>

                            <!-- Category -->
                            <div>

                                <label class="block text-sm font-medium mb-2">
                                    Category <span class="text-red-500">*</span>
                                </label>

                                <div class="flex gap-2">

                                    <select name="dd_category"
                                        id="categoryDropdown"
                                        required
                                        class="flex-1 px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">

                                        <option value="">Select Category</option>

                                        @foreach($categories as $category)

                                            <option value="{{ $category->category_id }}"
                                                {{ old('dd_category')==$category->category_id ? 'selected' : '' }}>

                                                {{ $category->category_name }}

                                            </option>

                                        @endforeach

                                    </select>

                                    <button
                                        type="button"
                                        onclick="openCategoryModal()"
                                        class="px-4 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white">

                                        <i class="ti ti-plus"></i>

                                    </button>

                                </div>

                            </div>

                            <!-- Purchase Price -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Purchase Price
                                </label>

                                <input type="number"
                                    step="0.01"
                                    name="tb_purchase_price"
                                    value="{{ old('tb_purchase_price') }}"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                            </div>

                            <!-- Selling Price -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Selling Price
                                </label>

                                <input type="number"
                                    step="0.01"
                                    name="tb_selling_price"
                                    value="{{ old('tb_selling_price') }}"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                            </div>

                            <!-- Reorder Level -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Reorder Level
                                </label>

                                <input type="number"
                                    name="tb_reorder_level"
                                    value="{{ old('tb_reorder_level') }}"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Status
                                </label>

                                <select name="dd_status"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">

                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>

                                </select>
                            </div>

                        </div>

                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800">

                        <div class="flex justify-end gap-3">

                            <a href="{{ route('products.index') }}"
                                class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700">

                                Cancel

                            </a>

                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white">

                                <i class="ti ti-device-floppy mr-1"></i>

                                Save Product

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </main>

    </div>

</div>

<!-- Category Modal -->
<div id="categoryModal"
    class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">

    <div class="bg-white dark:bg-zinc-900 rounded-2xl w-full max-w-md">

        <form action="{{ route('category.store.ajax') }}"
            method="POST"
            id="categoryForm">

            @csrf

            <div class="p-6">

                <div class="flex justify-between items-center mb-5">

                    <h2 class="text-xl font-bold">
                        Add Category
                    </h2>

                    <button type="button"
                        onclick="closeCategoryModal()">

                        <i class="ti ti-x text-xl"></i>

                    </button>

                </div>

                <div class="mb-4">

                    <label class="block text-sm mb-2">
                        Category Name
                    </label>

                    <input type="text"
                        name="category_name"
                        required
                        class="w-full px-4 py-3 rounded-xl border dark:bg-zinc-800">

                </div>

                <div class="mb-4">

                    <label class="block text-sm mb-2">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="w-full px-4 py-3 rounded-xl border dark:bg-zinc-800"></textarea>

                </div>

                <div class="flex justify-end gap-2">

                    <button
                        type="button"
                        onclick="closeCategoryModal()"
                        class="px-5 py-2 rounded-xl border">

                        Cancel

                    </button>

                    <button
                        class="px-5 py-2 rounded-xl bg-cyan-600 text-white">

                        Save Category

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<script>

function openCategoryModal() {
    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

document.getElementById('categoryForm').addEventListener('submit', function(e){

    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    fetch("{{ route('category.store.ajax') }}",{

        method:"POST",

        headers:{
            "X-CSRF-TOKEN":"{{ csrf_token() }}",
            "Accept":"application/json"
        },

        body:formData

    })
    .then(response=>response.json())
    .then(data=>{

        if(data.success){

            let dropdown = document.getElementById("categoryDropdown");

            let option = document.createElement("option");

            option.value = data.category.category_id;
            option.text = data.category.category_name;
            option.selected = true;

            dropdown.appendChild(option);

            form.reset();

            closeCategoryModal();

        }else{

            alert("Unable to save category.");

        }

    })
    .catch(error=>{

        console.log(error);
        alert("Something went wrong.");

    });

});

</script>

@endsection