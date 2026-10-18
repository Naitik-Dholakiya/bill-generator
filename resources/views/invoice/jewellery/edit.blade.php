@extends('layouts.app')

@section('title', 'Edit Jewellery Voucher')

@section('content')
<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
    @include('layout.sidebar')

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        @include('layout.navbar', ['title' => 'Edit Jewellery Voucher'])

        <main class="flex-1 overflow-y-auto p-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('invoice.index') }}" class="text-sm font-medium text-amber-700 hover:text-amber-800 dark:text-amber-300">← Jewellery vouchers</a>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit jewellery voucher</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Record pieces, weights, touch, wastage and customer gold balance.</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                    Fine weight is calculated as net weight × (touch + wastage) ÷ 100.
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
                    <p class="font-semibold">Please correct the following:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('invoice.update', $invoice->invoice_id) }}" method="POST" id="jewelleryInvoiceForm" class="space-y-6">
                @csrf
                @method('PUT')

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Voucher details</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Voucher number
                            <input name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number ?? $invoiceNumber) }}" readonly class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Party <span class="text-red-500">*</span>
                            <select name="customer_id" required class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <option value="">Select customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->customer_id }}" @selected(old('customer_id', $invoice->customer_id ?? '') == $customer->customer_id)>{{ $customer->customer_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date <span class="text-red-500">*</span>
                            <input type="date" name="invoice_date" required value="{{ old('invoice_date', isset($invoice) ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : date('Y-m-d')) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Voucher section
                            <input name="voucher_section" maxlength="50" required value="{{ old('voucher_section', $invoice->voucher_section ?? 'SALES') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 uppercase dark:border-zinc-700 dark:bg-zinc-800">
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Remarks
                            <input name="notes" value="{{ old('notes', $invoice->notes ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        </label>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Voucher footer note
                            <input name="footer_note" maxlength="500" value="{{ old('footer_note', $invoice->footer_note ?? '') }}" placeholder="માલ પરત લેવા મા આવશે નહિ" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        </label>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Jewellery items</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the actual piece count and weights for this sale.</p>
                        </div>
                        <button type="button" id="addJewelleryItem" class="rounded-xl bg-amber-600 px-4 py-2.5 font-medium text-white hover:bg-amber-700">+ Add item</button>
                    </div>
                    <div class="overflow-x-auto p-4">
                        <table class="min-w-[1180px] w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-zinc-800 dark:text-gray-400">
                                    <th class="p-3">Product</th><th class="p-3">Pcs</th><th class="p-3">Gross wt</th><th class="p-3">Net wt</th>
                                    <th class="p-3">Touch %</th><th class="p-3">Wastage %</th><th class="p-3">Fine wt</th><th></th>
                                </tr>
                            </thead>
                            <tbody id="jewelleryItems">
                                @foreach ($rows as $index => $item)
                                    @include('invoice.jewellery.item-row', ['item' => $item, 'index' => $index])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <template id="jewelleryItemTemplate">
                    @include('invoice.jewellery.item-row', ['item' => (object) [], 'index' => '__INDEX__'])
                </template>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="mb-5 font-semibold text-gray-900 dark:text-white">Gold calculation</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="text-sm text-gray-600 dark:text-gray-300">Less / kasar (g)
                                <input type="number" name="less_kasar" min="0" step="0.001" value="{{ old('less_kasar', $invoice->less_kasar ?? '') }}" class="voucher-number mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                            </label>
                            <label class="text-sm text-gray-600 dark:text-gray-300">Rate cut weight (g)
                                <input type="number" name="rate_cut_weight" min="0" step="0.001" value="{{ old('rate_cut_weight', $invoice->rate_cut_weight ?? '') }}" class="voucher-number mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                            </label>
                            <label class="text-sm text-gray-600 dark:text-gray-300 sm:col-span-2">Other converted fine (g)
                                <input type="number" name="other_convert_fine" min="0" step="0.001" value="{{ old('other_convert_fine', $invoice->other_convert_fine ?? '') }}" class="voucher-number mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <h2 class="mb-5 font-semibold text-gray-900 dark:text-white">Party gold balance</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="text-sm text-gray-600 dark:text-gray-300">Opening gold (g) · Credit
                                <input type="number" name="opening_gold" min="0" step="0.001" value="{{ old('opening_gold', $invoice->opening_gold ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                            </label>
                            <label class="text-sm text-gray-600 dark:text-gray-300">Closing gold (g) · Debit
                                <input type="number" name="closing_gold" min="0" step="0.001" value="{{ old('closing_gold', $invoice->closing_gold ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                            </label>
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-5 text-lg font-bold text-gray-900 dark:border-zinc-800 dark:text-white">Total fine: <span id="jewelleryFineTotal">0.000 g</span></div>
                    </div>
                </section>

                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route('invoice.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800">Cancel</a>
                    <button type="submit" class="rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">Save voucher</button>
                </div>
            </form>
        </main>

        @include('layout.footer')
    </div>
</div>

<script>
    const itemBody = document.getElementById('jewelleryItems');
    const numericValue = value => Number.parseFloat(value || '0') || 0;

    function calculateJewelleryRow(row) {
        const netWeight = numericValue(row.querySelector('.net-weight').value);
        const touch = numericValue(row.querySelector('.touch-value').value);
        const wastage = numericValue(row.querySelector('.wastage-value').value);
        row.querySelector('.fine-weight').value = (netWeight * (touch + wastage) / 100).toFixed(3);
    }

    function calculateJewelleryTotals() {
        let totalFine = 0;
        itemBody.querySelectorAll('.jewellery-item').forEach(row => {
            calculateJewelleryRow(row);
            totalFine += numericValue(row.querySelector('.fine-weight').value);
        });
        document.getElementById('jewelleryFineTotal').textContent = totalFine.toFixed(3) + ' g';
    }

    itemBody.addEventListener('input', event => {
        if (event.target.matches('input')) calculateJewelleryTotals();
    });
    itemBody.addEventListener('change', event => {
        if (event.target.matches('.product-select')) {
            const row = event.target.closest('.jewellery-item');
            const option = event.target.selectedOptions[0];
            row.querySelector('.touch-value').value = option.dataset.touch || '';
            row.querySelector('.wastage-value').value = option.dataset.wastage || '';
            calculateJewelleryTotals();
        }
    });
    document.querySelectorAll('.voucher-number').forEach(input => input.addEventListener('input', calculateJewelleryTotals));

    document.getElementById('addJewelleryItem').addEventListener('click', () => {
        const template = document.getElementById('jewelleryItemTemplate');
        const clone = template.content.cloneNode(true);
        const index = Date.now().toString() + Math.floor(Math.random() * 1000);
        clone.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('__INDEX__', index);
        });
        itemBody.appendChild(clone);
        calculateJewelleryTotals();
    });

    itemBody.addEventListener('click', event => {
        const button = event.target.closest('.remove-jewellery-item');
        if (!button) return;
        if (itemBody.querySelectorAll('.jewellery-item').length <= 1) {
            alert('At least one jewellery item is required.');
            return;
        }
        button.closest('.jewellery-item').remove();
        calculateJewelleryTotals();
    });

    calculateJewelleryTotals();
</script>
@endsection
