@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')

    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

        @include('layout.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">

            @include('layout.navbar', ['title' => 'Customer Details'])

            <main class="flex-1 overflow-y-auto p-6">

                <!-- Header -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">

                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $customer->customer_name }}
                        </h1>

                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            View customer information and account details.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">

                        <!-- Edit -->
                        <a href="{{ route('editCustomer', $customer->customer_id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                                bg-amber-500 hover:bg-amber-600
                                text-white font-medium transition">

                            <i class="ti ti-edit"></i>
                            Edit
                        </a>

                        <!-- Delete -->
                        <form action="{{ route('deleteCustomer', $customer->customer_id) }}" method="POST"
                            onsubmit="return confirm('Move this customer to trash?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                                    bg-red-500 hover:bg-red-600
                                    text-white font-medium transition">

                                <i class="ti ti-trash"></i>
                                Delete

                            </button>

                        </form>

                        <!-- Permanent Delete -->
                        {{-- <form action="{{ route('permanentDeleteCustomer', $customer->customer_id) }}" method="POST"
                            onsubmit="return confirm('This action cannot be undone. Permanently delete this customer?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                                    bg-red-800 hover:bg-red-900
                                    text-white font-medium transition">

                                <i class="ti ti-trash-x"></i>
                                Permanent Delete

                            </button>

                        </form> --}}

                    </div>

                </div>

                <!-- Customer Profile -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 mb-6">

                    <div class="flex items-center gap-4">

                        <div
                            class="w-20 h-20 rounded-full bg-cyan-100 dark:bg-cyan-500/10 flex items-center justify-center">

                            <i class="ti ti-user text-cyan-600 text-4xl"></i>

                        </div>

                        <div>

                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $customer->customer_name }}
                            </h2>

                            <p class="text-gray-500 dark:text-gray-400">
                                Customer Code : {{ $customer->customer_code }}
                            </p>

                        </div>

                    </div>

                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Customer Code
                        </p>

                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            {{ $customer->customer_code }}
                        </p>

                    </div>

                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            GST Number
                        </p>

                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            {{ $customer->gst_number ?: 'N/A' }}
                        </p>

                    </div>

                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Phone Number
                        </p>

                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            {{ $customer->phone ?: 'N/A' }}
                        </p>

                    </div>

                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Email Address
                        </p>

                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1 truncate">
                            {{ $customer->email ?: 'N/A' }}
                        </p>

                    </div>

                </div>

                <!-- Customer Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Information Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Customer Information
                        </h3>

                        <div class="space-y-5">

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Customer Name
                                </p>

                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $customer->customer_name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Customer Code
                                </p>

                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $customer->customer_code }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Email Address
                                </p>

                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $customer->email ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Phone Number
                                </p>

                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $customer->phone ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    GST Number
                                </p>

                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $customer->gst_number ?: '-' }}
                                </p>
                            </div>

                        </div>

                    </div>

                    <!-- Address Information -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Address Information
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Billing Address -->
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-500/10 flex items-center justify-center">
                                        <i class="ti ti-file-invoice text-cyan-600"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        Billing Address
                                    </h4>
                                </div>
                                <div
                                    class="bg-gray-50 dark:bg-zinc-800 rounded-xl p-4 min-h-[180px] border border-gray-100 dark:border-zinc-700">
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                        {{ $customer->billing_address ?: 'No Billing Address Available' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Shipping Address -->
                            <div>

                                <div class="flex items-center gap-2 mb-3">

                                    <div
                                        class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-500/10 flex items-center justify-center">
                                        <i class="ti ti-truck-delivery text-green-600"></i>
                                    </div>

                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        Shipping Address
                                    </h4>

                                </div>

                                <div
                                    class="bg-gray-50 dark:bg-zinc-800 rounded-xl p-4 min-h-[180px] border border-gray-100 dark:border-zinc-700">

                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                        {{ $customer->shipping_address ?: 'No Shipping Address Available' }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- System Information -->
                <div class="mt-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        System Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Created At
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $customer->created_at ? $customer->created_at->format('d M Y h:i A') : '-' }}
                            </p>

                        </div>

                        <div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Last Updated
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $customer->updated_at ? $customer->updated_at->format('d M Y h:i A') : '-' }}
                            </p>

                        </div>

                    </div>

                </div>

            </main>

        </div>

    </div>

@endsection