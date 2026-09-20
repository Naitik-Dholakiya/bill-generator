@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Create Invoice'])

            <main class="flex-1 overflow-y-auto p-6">

                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Create Invoice
                    </h1>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Create an invoice and generate the invoice PDF.
                    </p>
                </div>

                <form action="{{ route('invoice.generatePdf') }}" method="POST" id="invoiceForm">
                    @csrf

                    <!-- Invoice Information -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden mb-6">

                        <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Invoice Information
                            </h2>
                        </div>

                        <div class="p-6">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                <!-- Invoice Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Invoice Number
                                    </label>

                                    <input type="text"
                                        name="invoice_number"
                                        value="{{ $invoiceNumber ?? 'Auto Generated' }}"
                                        readonly
                                        class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 cursor-not-allowed">
                                </div>

                                <!-- Customer -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Customer <span class="text-red-500">*</span>
                                    </label>

                                    <select name="customer_id"
                                        required
                                        class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                        <option value="">Select Customer</option>

                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->customer_id }}"
                                                {{ old('customer_id') == $customer->customer_id ? 'selected' : '' }}>
                                                {{ $customer->customer_name }}
                                            </option>
                                        @endforeach

                                    </select>

                                    @error('customer_id')
                                        <p class="text-red-500 text-sm mt-1">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Invoice Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Invoice Date <span class="text-red-500">*</span>
                                    </label>

                                    <input type="date"
                                        name="invoice_date"
                                        value="{{ old('invoice_date', date('Y-m-d')) }}"
                                        required
                                        class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                    @error('invoice_date')
                                        <p class="text-red-500 text-sm mt-1">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>

                        </div>
                    </div>


                    <!-- Invoice Items -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden mb-6">

                        <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between">

                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Invoice Items
                                </h2>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Add products to this invoice.
                                </p>
                            </div>

                            <button type="button"
                                onclick="addItem()"
                                class="px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-medium">
                                <i class="ti ti-plus mr-1"></i>
                                Add Item
                            </button>

                        </div>

                        <div class="p-6">

                            <div class="overflow-x-auto">

                                <table class="w-full text-sm">

                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-zinc-800">

                                            <th class="text-left py-3 px-2 text-gray-600 dark:text-gray-400">
                                                Product
                                            </th>

                                            <th class="text-left py-3 px-2 text-gray-600 dark:text-gray-400">
                                                Quantity
                                            </th>

                                            <th class="text-left py-3 px-2 text-gray-600 dark:text-gray-400">
                                                Unit Price
                                            </th>

                                            <th class="text-left py-3 px-2 text-gray-600 dark:text-gray-400">
                                                Tax
                                            </th>

                                            <th class="text-left py-3 px-2 text-gray-600 dark:text-gray-400">
                                                Discount
                                            </th>

                                            <th class="text-right py-3 px-2 text-gray-600 dark:text-gray-400">
                                                Total
                                            </th>

                                            <th class="text-center py-3 px-2"></th>

                                        </tr>
                                    </thead>

                                    <tbody id="invoiceItems">

                                        <!-- First Item -->
                                        <tr class="invoice-item border-b border-gray-100 dark:border-zinc-800">

                                            <!-- Product -->
                                            <td class="py-4 px-2">

                                                <select name="items[0][product_id]"
                                                    onchange="updatePrice(this)"
                                                    required
                                                    class="product-select w-full min-w-[200px] px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                                    <option value="">Select Product</option>

                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->product_id }}"
                                                            data-price="{{ $product->selling_price }}">
                                                            {{ $product->product_name }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                            </td>

                                            <!-- Quantity -->
                                            <td class="py-4 px-2">

                                                <input type="number"
                                                    name="items[0][quantity]"
                                                    value="1"
                                                    min="1"
                                                    required
                                                    oninput="calculateRow(this)"
                                                    class="quantity w-24 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                            </td>

                                            <!-- Unit Price -->
                                            <td class="py-4 px-2">

                                                <input type="number"
                                                    name="items[0][unit_price]"
                                                    value="0.00"
                                                    min="0"
                                                    step="0.01"
                                                    required
                                                    oninput="calculateRow(this)"
                                                    class="unit-price w-32 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                            </td>

                                            <!-- Tax -->
                                            <td class="py-4 px-2">

                                                <input type="number"
                                                    name="items[0][tax_amount]"
                                                    value="0.00"
                                                    min="0"
                                                    step="0.01"
                                                    oninput="calculateRow(this)"
                                                    class="tax w-28 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                            </td>

                                            <!-- Discount -->
                                            <td class="py-4 px-2">

                                                <input type="number"
                                                    name="items[0][discount_amount]"
                                                    value="0.00"
                                                    min="0"
                                                    step="0.01"
                                                    oninput="calculateRow(this)"
                                                    class="discount w-28 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                                            </td>

                                            <!-- Total -->
                                            <td class="py-4 px-2 text-right">

                                                <input type="text"
                                                    name="items[0][total_amount]"
                                                    value="0.00"
                                                    readonly
                                                    class="row-total w-28 text-right px-3 py-2.5 rounded-lg bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">

                                            </td>

                                            <!-- Remove -->
                                            <td class="py-4 px-2 text-center">

                                                <button type="button"
                                                    onclick="removeItem(this)"
                                                    class="text-red-500 hover:text-red-700">

                                                    <i class="ti ti-trash text-lg"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>
                    </div>


                    <!-- Invoice Summary -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden mb-6">

                        <div class="p-6">

                            <div class="flex justify-end">

                                <div class="w-full md:w-96 space-y-4">

                                    <!-- Subtotal -->
                                    <div class="flex justify-between items-center">

                                        <span class="text-gray-600 dark:text-gray-400">
                                            Subtotal
                                        </span>

                                        <span id="subtotalDisplay"
                                            class="font-medium text-gray-900 dark:text-white">
                                            ₹0.00
                                        </span>

                                    </div>

                                    <!-- Tax -->
                                    <div class="flex justify-between items-center">

                                        <span class="text-gray-600 dark:text-gray-400">
                                            Total Tax
                                        </span>

                                        <span id="taxDisplay"
                                            class="font-medium text-gray-900 dark:text-white">
                                            ₹0.00
                                        </span>

                                    </div>

                                    <!-- Discount -->
                                    <div class="flex justify-between items-center">

                                        <span class="text-gray-600 dark:text-gray-400">
                                            Discount
                                        </span>

                                        <span id="discountDisplay"
                                            class="font-medium text-gray-900 dark:text-white">
                                            ₹0.00
                                        </span>

                                    </div>

                                    <div class="border-t border-gray-200 dark:border-zinc-800 pt-4"></div>

                                    <!-- Grand Total -->
                                    <div class="flex justify-between items-center">

                                        <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                            Grand Total
                                        </span>

                                        <span id="grandTotalDisplay"
                                            class="text-xl font-bold text-cyan-600">
                                            ₹0.00
                                        </span>

                                    </div>

                                    <!-- Hidden Summary Fields -->
                                    <input type="hidden" name="subtotal" id="subtotal">
                                    <input type="hidden" name="total_tax" id="total_tax">
                                    <input type="hidden" name="discount_amount" id="discount_amount">
                                    <input type="hidden" name="grand_total" id="grand_total">

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Notes -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden mb-6">

                        <div class="p-6">

                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Notes
                            </label>

                            <textarea name="notes"
                                rows="4"
                                placeholder="Add any additional notes for this invoice..."
                                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">{{ old('notes') }}</textarea>

                            @error('notes')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>


                    <!-- Actions -->
                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm">

                        <div class="px-6 py-4">

                            <div class="flex items-center justify-end gap-3">

                                <a href="{{ route('invoice.index') }}"
                                    class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800">

                                    Cancel

                                </a>

                                <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-medium">

                                    <i class="ti ti-file-type-pdf mr-1"></i>

                                    Generate Invoice PDF

                                </button>

                            </div>

                        </div>

                    </div>

                </form>

            </main>
        </div>
    </div>


    <!-- JavaScript -->
    <script>

        let itemIndex = 1;

        function addItem() {

            const tbody = document.getElementById('invoiceItems');

            const row = document.createElement('tr');

            row.className =
                'invoice-item border-b border-gray-100 dark:border-zinc-800';

            row.innerHTML = `

                <td class="py-4 px-2">

                    <select
                        name="items[${itemIndex}][product_id]"
                        onchange="updatePrice(this)"
                        required
                        class="product-select w-full min-w-[200px] px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                        <option value="">Select Product</option>

                        @foreach ($products as $product)
                            <option value="{{ $product->product_id }}"
                                data-price="{{ $product->selling_price }}">
                                {{ $product->product_name }}
                            </option>
                        @endforeach

                    </select>

                </td>

                <td class="py-4 px-2">

                    <input
                        type="number"
                        name="items[${itemIndex}][quantity]"
                        value="1"
                        min="1"
                        required
                        oninput="calculateRow(this)"
                        class="quantity w-24 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                </td>

                <td class="py-4 px-2">

                    <input
                        type="number"
                        name="items[${itemIndex}][unit_price]"
                        value="0.00"
                        min="0"
                        step="0.01"
                        required
                        oninput="calculateRow(this)"
                        class="unit-price w-32 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                </td>

                <td class="py-4 px-2">

                    <input
                        type="number"
                        name="items[${itemIndex}][tax_amount]"
                        value="0.00"
                        min="0"
                        step="0.01"
                        oninput="calculateRow(this)"
                        class="tax w-28 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                </td>

                <td class="py-4 px-2">

                    <input
                        type="number"
                        name="items[${itemIndex}][discount_amount]"
                        value="0.00"
                        min="0"
                        step="0.01"
                        oninput="calculateRow(this)"
                        class="discount w-28 px-3 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 focus:ring-2 focus:ring-cyan-500 outline-none">

                </td>

                <td class="py-4 px-2 text-right">

                    <input
                        type="text"
                        name="items[${itemIndex}][total_amount]"
                        value="0.00"
                        readonly
                        class="row-total w-28 text-right px-3 py-2.5 rounded-lg bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">

                </td>

                <td class="py-4 px-2 text-center">

                    <button
                        type="button"
                        onclick="removeItem(this)"
                        class="text-red-500 hover:text-red-700">

                        <i class="ti ti-trash text-lg"></i>

                    </button>

                </td>
            `;

            tbody.appendChild(row);

            itemIndex++;

        }


        function removeItem(button) {

            const rows = document.querySelectorAll('.invoice-item');

            if (rows.length <= 1) {

                alert('At least one invoice item is required.');

                return;

            }

            button.closest('.invoice-item').remove();

            calculateTotals();

        }


        function updatePrice(select) {

            const row = select.closest('.invoice-item');

            const selectedOption =
                select.options[select.selectedIndex];

            const price =
                selectedOption.dataset.price || 0;

            row.querySelector('.unit-price').value =
                parseFloat(price).toFixed(2);

            calculateRow(select);

        }


        function calculateRow(element) {

            const row =
                element.closest('.invoice-item');

            const quantity =
                parseFloat(row.querySelector('.quantity').value) || 0;

            const unitPrice =
                parseFloat(row.querySelector('.unit-price').value) || 0;

            const tax =
                parseFloat(row.querySelector('.tax').value) || 0;

            const discount =
                parseFloat(row.querySelector('.discount').value) || 0;

            const subtotal =
                quantity * unitPrice;

            const total =
                subtotal + tax - discount;

            row.querySelector('.row-total').value =
                Math.max(total, 0).toFixed(2);

            calculateTotals();

        }


        function calculateTotals() {

            let subtotal = 0;
            let tax = 0;
            let discount = 0;
            let grandTotal = 0;

            document.querySelectorAll('.invoice-item').forEach(row => {

                const quantity =
                    parseFloat(row.querySelector('.quantity').value) || 0;

                const unitPrice =
                    parseFloat(row.querySelector('.unit-price').value) || 0;

                const rowTax =
                    parseFloat(row.querySelector('.tax').value) || 0;

                const rowDiscount =
                    parseFloat(row.querySelector('.discount').value) || 0;

                const rowSubtotal =
                    quantity * unitPrice;

                subtotal += rowSubtotal;

                tax += rowTax;

                discount += rowDiscount;

            });

            grandTotal =
                subtotal + tax - discount;

            grandTotal =
                Math.max(grandTotal, 0);

            document.getElementById('subtotalDisplay').innerText =
                '₹' + subtotal.toFixed(2);

            document.getElementById('taxDisplay').innerText =
                '₹' + tax.toFixed(2);

            document.getElementById('discountDisplay').innerText =
                '₹' + discount.toFixed(2);

            document.getElementById('grandTotalDisplay').innerText =
                '₹' + grandTotal.toFixed(2);


            document.getElementById('subtotal').value =
                subtotal.toFixed(2);

            document.getElementById('total_tax').value =
                tax.toFixed(2);

            document.getElementById('discount_amount').value =
                discount.toFixed(2);

            document.getElementById('grand_total').value =
                grandTotal.toFixed(2);

        }


        document.addEventListener('DOMContentLoaded', function () {

            calculateTotals();

        });

    </script>

@endsection