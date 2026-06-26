@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')

    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

        ```
        @include('layout.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">

            @include('layout.navbar', ['title' => 'Edit Customer'])

            <main class="flex-1 overflow-y-auto p-6">

                <!-- Page Header -->
                <div class="mb-6">

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Edit Customer
                    </h1>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Update customer information.
                    </p>

                </div>

                <form action="{{ route('editCustomerPost', $customer->customer_id) }}" method="PUT">

                    @csrf
                    @method('PUT')

                    <div
                        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">

                        <!-- Card Header -->
                        <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">

                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Customer Information
                            </h2>

                        </div>

                        <!-- Form -->
                        <div class="p-6">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Customer Code -->
                                <div>

                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Customer Code
                                    </label>

                                    <input type="text" value="{{ $customer->customer_code }}" readonly class="w-full px-4 py-3 rounded-xl
                                    bg-gray-100 dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    cursor-not-allowed">

                                </div>

                                <!-- Customer Name -->
                                <div>

                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Customer Name <span class="text-red-500">*</span>
                                    </label>

                                    <input type="text" name="customer_name"
                                        value="{{ old('customer_name', $customer->customer_name) }}" required class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500
                                    outline-none">

                                    @error('customer_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror

                                </div>

                                <!-- Email -->
                                <div>

                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email
                                    </label>

                                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500
                                    outline-none">

                                </div>

                                <!-- Phone -->
                                <div>

                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Phone Number
                                    </label>

                                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full px-4 py-3 rounded-xl
                                    bg-white dark:bg-zinc-800
                                    border border-gray-200 dark:border-zinc-700
                                    focus:ring-2 focus:ring-cyan-500
                                    outline-none">

                                </div>

                            </div>

                            <!-- GST Number -->
                            <div class="mt-6">

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    GST Number
                                </label>

                                <input type="text" name="gst_number" value="{{ old('gst_number', $customer->gst_number) }}"
                                    class="w-full px-4 py-3 rounded-xl
                                bg-white dark:bg-zinc-800
                                border border-gray-200 dark:border-zinc-700
                                focus:ring-2 focus:ring-cyan-500
                                outline-none">

                            </div>

                            <!-- Billing Address -->
                            <div class="mt-6">

                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Billing Address
                                </label>

                                <textarea id="billing_address" name="billing_address" rows="4" class="w-full px-4 py-3 rounded-xl
                                bg-white dark:bg-zinc-800
                                border border-gray-200 dark:border-zinc-700
                                focus:ring-2 focus:ring-cyan-500
                                outline-none">{{ old('billing_address', $customer->billing_address) }}</textarea>

                            </div>

                            <!-- Shipping Address -->
                            <div class="mt-6">

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-2">

                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Shipping Address
                                    </label>

                                    <label class="inline-flex items-center gap-2 cursor-pointer">

                                        <input type="checkbox" id="sameAsBilling" class="w-4 h-4 text-cyan-600 rounded">

                                        <span class="text-sm font-medium text-cyan-600 dark:text-cyan-400">
                                            Same as Billing Address
                                        </span>

                                    </label>

                                </div>

                                <textarea id="shipping_address" name="shipping_address" rows="4" class="w-full px-4 py-3 rounded-xl
                                bg-white dark:bg-zinc-800
                                border border-gray-200 dark:border-zinc-700
                                focus:ring-2 focus:ring-cyan-500
                                outline-none">{{ old('shipping_address', $customer->shipping_address) }}</textarea>

                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800">

                            <div class="flex justify-end gap-3">

                                <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-xl
                                border border-gray-300 dark:border-zinc-700
                                text-gray-700 dark:text-gray-300
                                hover:bg-gray-100 dark:hover:bg-zinc-800">

                                    Cancel

                                </a>

                                <button type="submit" class="px-5 py-2.5 rounded-xl
                                bg-cyan-600 hover:bg-cyan-700
                                text-white font-medium">

                                    <i class="ti ti-device-floppy mr-1"></i>
                                    Update Customer

                                </button>

                            </div>

                        </div>

                    </div>

                </form>

            </main>

        </div>
        ```

    </div>

    @push('scripts')

        <script>

            document.addEventListener('DOMContentLoaded', function () {

                const billingAddress = document.getElementById('billing_address');
                const shippingAddress = document.getElementById('shipping_address');
                const sameAsBilling = document.getElementById('sameAsBilling');

                function syncShippingAddress() {

                    if (sameAsBilling.checked) {

                        shippingAddress.value = billingAddress.value;
                        shippingAddress.readOnly = true;

                    } else {

                        shippingAddress.readOnly = false;

                    }
                }

                sameAsBilling.addEventListener('change', syncShippingAddress);

                billingAddress.addEventListener('input', function () {

                    if (sameAsBilling.checked) {
                        shippingAddress.value = billingAddress.value;
                    }

                });

            });

        </script>

    @endpush

@endsection