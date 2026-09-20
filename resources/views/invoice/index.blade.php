@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

        @include('layout.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">

            @include('layout.navbar', ['title' => 'Invoices'])

            <main class="flex-1 overflow-y-auto p-6">

                <!-- Page Header -->
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Invoices
                        </h1>

                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Manage customer invoices and billing records.
                        </p>
                    </div>

                    <a href="{{ route('invoice.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-medium transition">

                        <i class="ti ti-plus mr-2"></i>

                        Create Invoice

                    </a>

                </div>


                <!-- Success Message -->
                @if (session('success'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900 text-green-700 dark:text-green-400">

                        <div class="flex items-center gap-2">

                            <i class="ti ti-circle-check text-lg"></i>

                            <span>
                                {{ session('success') }}
                            </span>

                        </div>

                    </div>
                @endif


                <!-- Error Message -->
                @if (session('error'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400">

                        <div class="flex items-center gap-2">

                            <i class="ti ti-alert-circle text-lg"></i>

                            <span>
                                {{ session('error') }}
                            </span>

                        </div>

                    </div>
                @endif


                <!-- Invoice Table -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">

                    <!-- Table Header -->
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Invoice List
                                </h2>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    View and manage generated invoices.
                                </p>
                            </div>


                            <!-- Search -->
                            <div class="relative">

                                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                                <input type="text" id="invoiceSearch" placeholder="Search invoice..."
                                    class="w-full md:w-64 pl-10 pr-4 py-2.5 rounded-xl bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-cyan-500 outline-none">

                            </div>

                        </div>

                    </div>


                    <!-- Responsive Table -->
                    <div class="overflow-x-auto">

                        <table class="w-full text-sm" id="invoiceTable">

                            <thead>

                                <tr class="bg-gray-50 dark:bg-zinc-800/50 border-b border-gray-200 dark:border-zinc-800">

                                    <th class="px-6 py-4 text-left font-semibold text-gray-600 dark:text-gray-400">
                                        #
                                    </th>

                                    <th class="px-6 py-4 text-left font-semibold text-gray-600 dark:text-gray-400">
                                        Invoice
                                    </th>

                                    <th class="px-6 py-4 text-left font-semibold text-gray-600 dark:text-gray-400">
                                        Customer
                                    </th>

                                    <th class="px-6 py-4 text-left font-semibold text-gray-600 dark:text-gray-400">
                                        Invoice Date
                                    </th>

                                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-400">
                                        Subtotal
                                    </th>

                                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-400">
                                        Tax
                                    </th>

                                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-400">
                                        Discount
                                    </th>

                                    <th class="px-6 py-4 text-right font-semibold text-gray-600 dark:text-gray-400">
                                        Grand Total
                                    </th>

                                    <th class="px-6 py-4 text-center font-semibold text-gray-600 dark:text-gray-400">
                                        Payment
                                    </th>

                                    <th class="px-6 py-4 text-center font-semibold text-gray-600 dark:text-gray-400">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">

                                @forelse ($invoices as $index => $invoice)
                                    <tr class="invoice-row hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">

                                        <!-- Serial Number -->
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">

                                            {{ $invoices->firstItem() + $index }}

                                        </td>


                                        <!-- Invoice Number -->
                                        <td class="px-6 py-4">

                                            <div class="font-semibold text-gray-900 dark:text-white">

                                                {{ $invoice->invoice_number }}

                                            </div>

                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">

                                                ID: {{ $invoice->invoice_id }}

                                            </div>

                                        </td>


                                        <!-- Customer -->
                                        <td class="px-6 py-4">

                                            <div class="font-medium text-gray-900 dark:text-white">

                                                {{ $invoice->customer_name ?? 'N/A' }}

                                            </div>

                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">

                                                Customer ID:
                                                {{ $invoice->customer_id }}

                                            </div>

                                        </td>


                                        <!-- Invoice Date -->
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">

                                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}

                                        </td>


                                        <!-- Subtotal -->
                                        <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap">

                                            ₹{{ number_format($invoice->subtotal, 2) }}

                                        </td>


                                        <!-- Tax -->
                                        <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap">

                                            ₹{{ number_format($invoice->total_tax, 2) }}

                                        </td>


                                        <!-- Discount -->
                                        <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap">

                                            ₹{{ number_format($invoice->discount_amount, 2) }}

                                        </td>


                                        <!-- Grand Total -->
                                        <td class="px-6 py-4 text-right">

                                            <span class="font-bold text-gray-900 dark:text-white whitespace-nowrap">

                                                ₹{{ number_format($invoice->grand_total, 2) }}

                                            </span>

                                        </td>


                                        <!-- Payment Status -->
                                        <td class="px-6 py-4 text-center">

                                            @if ($invoice->payment_status === 'paid')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">

                                                    <i class="ti ti-circle-check mr-1"></i>

                                                    Paid

                                                </span>
                                            @elseif ($invoice->payment_status === 'partial')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">

                                                    <i class="ti ti-clock mr-1"></i>

                                                    Partial

                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">

                                                    <i class="ti ti-alert-circle mr-1"></i>

                                                    Pending

                                                </span>
                                            @endif

                                        </td>


                                        <!-- Actions -->
                                        <td class="px-6 py-4">

                                            <div class="flex items-center justify-center gap-2">

                                                <!-- PDF -->
                                                <a href="{{ route('invoice.pdf', $invoice->invoice_id) }}" target="_blank"
                                                    title="View PDF"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-cyan-600 hover:text-cyan-700 hover:bg-cyan-50 dark:hover:bg-cyan-950/30 transition">

                                                    <i class="ti ti-file-type-pdf text-lg"></i>

                                                </a>


                                                <!-- View -->
                                                <a href="{{ route('invoice.pdf', $invoice->invoice_id) }}" target="_blank"
                                                    title="View Invoice"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-zinc-800 transition">

                                                    <i class="ti ti-eye text-lg"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="10" class="px-6 py-16 text-center">

                                            <div class="flex flex-col items-center justify-center">

                                                <div
                                                    class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-zinc-800 flex items-center justify-center mb-4">

                                                    <i class="ti ti-file-invoice text-3xl text-gray-400"></i>

                                                </div>

                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    No invoices found
                                                </h3>

                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-5">
                                                    Create your first invoice to get started.
                                                </p>

                                                <a href="{{ route('invoice.create') }}"
                                                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-medium">

                                                    <i class="ti ti-plus mr-2"></i>

                                                    Create Invoice

                                                </a>

                                            </div>

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    <!-- Pagination -->
                    @if ($invoices->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-zinc-800">

                            {{ $invoices->links() }}

                        </div>
                    @endif

                </div>

            </main>

        </div>

    </div>


    <!-- Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const searchInput =
                document.getElementById('invoiceSearch');

            const rows =
                document.querySelectorAll('.invoice-row');


            searchInput.addEventListener('input', function() {

                const search =
                    this.value.toLowerCase().trim();


                rows.forEach(function(row) {

                    const text =
                        row.textContent.toLowerCase();

                    if (text.includes(search)) {

                        row.style.display = '';

                    } else {

                        row.style.display = 'none';

                    }

                });

            });

        });
    </script>

@endsection
