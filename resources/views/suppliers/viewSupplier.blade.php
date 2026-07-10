@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')

<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        @include('layout.navbar', ['title' => 'Supplier Details'])

        <main class="flex-1 overflow-y-auto p-6">

            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $supplier->supplier_name }}
                    </h1>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        View supplier information and contact details.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">

                    <!-- Edit -->
                    <a href="{{ route('supplier.edit', $supplier->supplier_id) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                        bg-amber-500 hover:bg-amber-600
                        text-white font-medium transition">

                        <i class="ti ti-edit"></i>
                        Edit
                    </a>

                    <!-- Delete -->
                    <form action="
                    {{ route('supplier.delete', $supplier->supplier_id) }}" method="POST"
                        onsubmit="return confirm('Move this supplier to trash?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                            bg-red-500 hover:bg-red-600
                            text-white font-medium transition">

                            <i class="ti ti-trash"></i>
                            Delete
                        </button>

                    </form>

                </div>

            </div>

            <!-- Supplier Profile -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 mb-6">

                <div class="flex items-center gap-4">

                    <div
                        class="w-20 h-20 rounded-full bg-cyan-100 dark:bg-cyan-500/10 flex items-center justify-center">

                        <i class="ti ti-building-store text-cyan-600 text-4xl"></i>

                    </div>

                    <div>

                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $supplier->supplier_name }}
                        </h2>

                        <p class="text-gray-500 dark:text-gray-400">
                            Supplier Code : {{ $supplier->supplier_code }}
                        </p>

                    </div>

                </div>

            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Supplier Code
                    </p>

                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                        {{ $supplier->supplier_code }}
                    </p>

                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        GST Number
                    </p>

                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                        {{ $supplier->gst_number ?: 'N/A' }}
                    </p>

                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Phone Number
                    </p>

                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                        {{ $supplier->phone ?: 'N/A' }}
                    </p>

                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-5">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Email Address
                    </p>

                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1 truncate">
                        {{ $supplier->email ?: 'N/A' }}
                    </p>

                </div>

            </div>

            <!-- Supplier Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Information Card -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Supplier Information
                    </h3>

                    <div class="space-y-5">

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Supplier Name
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $supplier->supplier_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Supplier Code
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $supplier->supplier_code }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Contact Person
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $supplier->contact_person ?: '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Email Address
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $supplier->email ?: '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Phone Number
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $supplier->phone ?: '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                GST Number
                            </p>

                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $supplier->gst_number ?: '-' }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- Address -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Supplier Address
                    </h3>

                    <div
                        class="bg-gray-50 dark:bg-zinc-800 rounded-xl p-5 min-h-[220px] border border-gray-100 dark:border-zinc-700">

                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ $supplier->supplier_address ?: 'No Address Available' }}
                        </p>

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
                            {{ $supplier->created_at ? $supplier->created_at->format('d M Y h:i A') : '-' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Last Updated
                        </p>

                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $supplier->updated_at ? $supplier->updated_at->format('d M Y h:i A') : '-' }}
                        </p>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

@endsection