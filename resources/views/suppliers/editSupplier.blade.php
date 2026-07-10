@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')

<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        @include('layout.navbar', ['title' => 'Edit Supplier'])

        <main class="flex-1 overflow-y-auto p-6">

            <!-- Page Header -->
            <div class="mb-6">

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit Supplier
                </h1>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Update supplier information.
                </p>

            </div>

            <form action="{{ route('editSupplierPost', $supplier->supplier_id) }}" method="POST">

                @csrf
                @method('PUT')

                <div
                    class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">

                    <!-- Card Header -->
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Supplier Information
                        </h2>

                    </div>

                    <!-- Form Body -->
                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Supplier Code -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Supplier Code
                                </label>

                                <input type="text"
                                    value="{{ $supplier->supplier_code }}"
                                    readonly
                                    name="tb_supplierCode"
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-gray-100 dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    cursor-not-allowed">

                            </div>

                            <!-- Supplier Name -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Supplier Name <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="tb_supplierName"
                                    value="{{ old('tb_supplierName', $supplier->supplier_name) }}"
                                    required
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500
                                    outline-none">

                                @error('tb_supplierName')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Email -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email Address
                                </label>

                                <input type="email"
                                    name="tb_email"
                                    value="{{ old('tb_email', $supplier->email) }}"
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500
                                    outline-none">

                                @error('tb_email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Phone -->
                            <div>

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="tb_phone"
                                    value="{{ old('tb_phone', $supplier->phone) }}"
                                    required
                                    class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500
                                    outline-none">

                                @error('tb_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                        </div>

                        <!-- Contact Person -->
                        <div class="mt-6">

                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Person
                            </label>

                            <input type="text"
                                name="tb_contactPerson"
                                value="{{ old('tb_contactPerson', $supplier->contact_person) }}"
                                class="w-full px-4 py-3 rounded-xl
                                bg-white dark:bg-zinc-800
                                border border-gray-200 dark:border-zinc-700
                                focus:ring-2 focus:ring-cyan-500
                                outline-none">

                            @error('tb_contactPerson')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                        </div>

                        <!-- GST Number -->
                        <div class="mt-6">

                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                GST Number
                            </label>

                            <input type="text"
                                name="tb_gstNumber"
                                value="{{ old('tb_gstNumber', $supplier->gst_number) }}"
                                class="w-full px-4 py-3 rounded-xl
                                bg-white dark:bg-zinc-800
                                border border-gray-200 dark:border-zinc-700
                                focus:ring-2 focus:ring-cyan-500
                                outline-none">

                            @error('tb_gstNumber')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                        </div>

                        <!-- Supplier Address -->
                        <div class="mt-6">

                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Supplier Address
                            </label>

                            <textarea
                                name="tb_supplierAddress"
                                rows="5"
                                class="w-full px-4 py-3 rounded-xl
                                bg-white dark:bg-zinc-800
                                border border-gray-200 dark:border-zinc-700
                                focus:ring-2 focus:ring-cyan-500
                                outline-none">{{ old('tb_supplierAddress', $supplier->supplier_address) }}</textarea>

                            @error('tb_supplierAddress')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800">

                        <div class="flex justify-end gap-3">

                            <a href="{{ route('suppliers.index') }}"
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
                                Update Supplier

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </main>

    </div>

</div>

@endsection