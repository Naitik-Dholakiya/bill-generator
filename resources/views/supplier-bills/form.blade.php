@php
    $today = now()->toDateString();
    $productOptions = $products->map(fn ($product) => [
        'id' => $product->product_id,
        'label' => $product->product_code.' - '.$product->product_name,
        'price' => $product->purchase_price,
        'touch' => $product->touch,
        'wastage' => $product->default_wastage,
    ])->values();
@endphp

<form action="{{ $action }}" method="POST" id="supplierBillForm" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->has('stock'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
            {{ $errors->first('stock') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Bill Information</h2>
        </div>
        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="purchase-number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bill Number</label>
                <input id="purchase-number" type="text" value="{{ $bill->purchase_number ?? $billNumber }}" readonly
                    class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            </div>
            <div>
                <label for="supplier-id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier <span class="text-red-500">*</span></label>
                <select id="supplier-id" name="supplier_id" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="">Select supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->supplier_id }}" @selected(old('supplier_id', $bill->supplier_id ?? '') == $supplier->supplier_id)>
                            {{ $supplier->supplier_name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="purchase-date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bill Date <span class="text-red-500">*</span></label>
                <input id="purchase-date" type="date" name="purchase_date"
                    value="{{ old('purchase_date', isset($bill) ? $bill->purchase_date : $today) }}" required
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                @error('purchase_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="payment-status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Status <span class="text-red-500">*</span></label>
                <select id="payment-status" name="payment_status" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    @foreach (['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_status', $bill->payment_status ?? 'pending') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @if ($isJewelryUser)
                <div>
                    <label for="voucher-section" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Voucher Section <span class="text-red-500">*</span></label>
                    <input id="voucher-section" name="voucher_section" maxlength="50" required value="{{ old('voucher_section', $bill->voucher_section ?? 'PURCHASE') }}"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 uppercase dark:border-zinc-700 dark:bg-zinc-800">
                    @error('voucher_section')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif
            <div class="md:col-span-2 xl:col-span-4">
                <label for="bill-notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea id="bill-notes" name="notes" rows="2" maxlength="2000"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">{{ old('notes', $bill->notes ?? '') }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @if ($isJewelryUser)
                <div class="md:col-span-2 xl:col-span-4">
                    <label for="bill-footer-note" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bill Footer Note</label>
                    <input id="bill-footer-note" name="footer_note" maxlength="500" value="{{ old('footer_note', $bill->footer_note ?? '') }}"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    @error('footer_note')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif
        </div>
    </section>

    @if ($isJewelryUser)
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Jewellery Items</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter piece count and weights in grams. Fine weight is net weight × (touch + wastage) ÷ 100.</p>
                </div>
                <button type="button" id="addJewelleryItem" class="rounded-xl bg-amber-600 px-4 py-2.5 font-medium text-white hover:bg-amber-700">+ Add item</button>
            </div>
            <div class="overflow-x-auto p-4">
                <table class="min-w-[1180px] w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-zinc-800 dark:text-gray-400">
                            <th class="p-3">Product</th><th class="p-3">Pcs</th><th class="p-3">Gross wt (g)</th><th class="p-3">Net wt (g)</th>
                            <th class="p-3">Touch %</th><th class="p-3">Wastage %</th><th class="p-3">Fine wt (g)</th><th></th>
                        </tr>
                    </thead>
                    <tbody id="jewelleryItems">
                        @foreach ($rows as $index => $row)
                            @include('invoice.jewellery.item-row', ['item' => $row, 'index' => $index])
                        @endforeach
                    </tbody>
                </table>
            </div>
            <template id="jewelleryItemTemplate">
                @include('invoice.jewellery.item-row', ['item' => (object) [], 'index' => '__INDEX__'])
            </template>
            @foreach ($errors->getMessages() as $key => $messages)
                @if ($key === 'items' || str_starts_with($key, 'items.'))
                    <p class="px-6 pb-2 text-sm text-red-600">{{ $messages[0] }}</p>
                @endif
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-5 font-semibold text-gray-900 dark:text-white">Gold Calculation</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach (['less_kasar' => 'Less / kasar (g)', 'rate_cut_weight' => 'Rate cut weight (g)', 'other_convert_fine' => 'Other converted fine (g)'] as $name => $label)
                        <label class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}
                            <input type="number" name="{{ $name }}" min="0" step="0.001" value="{{ old($name, $bill->$name ?? '') }}"
                                class="gold-number mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                            @error($name)<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-5 font-semibold text-gray-900 dark:text-white">Supplier Gold Balance</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="text-sm text-gray-600 dark:text-gray-300">Opening gold (g) · Credit
                        <input type="number" name="opening_gold" min="0" step="0.001" value="{{ old('opening_gold', $bill->opening_gold ?? '') }}"
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                        @error('opening_gold')<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm text-gray-600 dark:text-gray-300">Closing gold (g) · Debit
                        <input type="number" name="closing_gold" min="0" step="0.001" value="{{ old('closing_gold', $bill->closing_gold ?? '') }}"
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                        @error('closing_gold')<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
                <div class="mt-6 border-t border-gray-200 pt-5 text-lg font-bold text-gray-900 dark:border-zinc-800 dark:text-white">Total fine: <span id="jewelleryFineTotal">0.000 g</span></div>
            </div>
        </section>
    @else
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Purchased Products</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Saving the bill adds these quantities to Stock.</p>
            </div>
            <button type="button" id="addBillItem" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                <i class="ti ti-plus mr-1"></i>Add item
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-800/70">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Product</th>
                        <th class="px-4 py-3 text-right font-semibold">Quantity</th>
                        <th class="px-4 py-3 text-right font-semibold">Unit Cost</th>
                        <th class="px-4 py-3 text-right font-semibold">Tax %</th>
                        <th class="px-4 py-3 text-right font-semibold">Discount</th>
                        <th class="px-4 py-3 text-right font-semibold">Line Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="billItems" class="divide-y divide-gray-100 dark:divide-zinc-800">
                    @foreach ($rows as $index => $row)
                        @include('supplier-bills.item-row', ['index' => $index, 'row' => $row, 'products' => $products])
                    @endforeach
                </tbody>
            </table>
        </div>
        <template id="billItemTemplate">
            @include('supplier-bills.item-row', ['index' => '__INDEX__', 'row' => (object) [], 'products' => $products])
        </template>
        <div class="space-y-2 border-t border-gray-200 px-6 py-4 text-right dark:border-zinc-800">
            <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal: ₹<span id="subtotal">0.00</span></p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Tax: ₹<span id="totalTax">0.00</span></p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Discount: ₹<span id="totalDiscount">0.00</span></p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Bill Total: ₹<span id="grandTotal">0.00</span></p>
        </div>
        @foreach ($errors->getMessages() as $key => $messages)
            @if ($key === 'items' || str_starts_with($key, 'items.'))
                <p class="px-6 pb-2 text-sm text-red-600">{{ $messages[0] }}</p>
            @endif
        @endforeach
        <div class="flex justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
            <a href="{{ route('supplier-bills.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800">Cancel</a>
            <button type="submit" @disabled($suppliers->isEmpty() || $products->isEmpty())
                class="rounded-xl bg-cyan-600 px-5 py-2.5 font-medium text-white hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50">
                {{ $isEdit ? 'Save Changes' : 'Save Supplier Bill' }}
            </button>
        </div>
    </section>
    @endif
</form>

@push('scripts')
<script>
(() => {
    const tbody = document.getElementById(@json($isJewelryUser ? 'jewelleryItems' : 'billItems'));
    const template = document.getElementById(@json($isJewelryUser ? 'jewelleryItemTemplate' : 'billItemTemplate'));
    const productOptions = @json($productOptions);
    let nextIndex = {{ count($rows) }};

    @if ($isJewelryUser)
    const numericValue = value => Number.parseFloat(value || '0') || 0;
    const jewelleryFineTotal = document.getElementById('jewelleryFineTotal');

    function calculateJewelleryTotals() {
        let totalFine = 0;
        tbody.querySelectorAll('.jewellery-item').forEach((row) => {
            const netWeight = numericValue(row.querySelector('.net-weight').value);
            const touch = numericValue(row.querySelector('.touch-value').value);
            const wastage = numericValue(row.querySelector('.wastage-value').value);
            const fineWeight = netWeight * (touch + wastage) / 100;
            row.querySelector('.fine-weight').value = fineWeight.toFixed(3);
            totalFine += fineWeight;
        });
        jewelleryFineTotal.textContent = totalFine.toFixed(3) + ' g';
    }

    function addJewelleryRow() {
        const html = document.getElementById('jewelleryItemTemplate').innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        tbody.insertAdjacentHTML('beforeend', html);
        calculateJewelleryTotals();
    }

    document.getElementById('addJewelleryItem').addEventListener('click', addJewelleryRow);
    tbody.addEventListener('input', calculateJewelleryTotals);
    tbody.addEventListener('change', (event) => {
        if (event.target.matches('.product-select')) {
            const product = productOptions.find((item) => String(item.id) === event.target.value);
            const row = event.target.closest('.jewellery-item');
            if (product && row) {
                row.querySelector('.touch-value').value = product.touch || '';
                row.querySelector('.wastage-value').value = product.wastage || '';
            }
        }
        calculateJewelleryTotals();
    });
    tbody.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-jewellery-item');
        if (!removeButton) return;
        if (tbody.querySelectorAll('.jewellery-item').length <= 1) {
            alert('At least one jewellery item is required.');
            return;
        }
        removeButton.closest('.jewellery-item').remove();
        calculateJewelleryTotals();
    });
    calculateJewelleryTotals();
    @else
    function addRow() {
        const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        tbody.insertAdjacentHTML('beforeend', html);
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;
        let tax = 0;
        let discount = 0;
        let grandTotal = 0;

        tbody.querySelectorAll('tr').forEach((row) => {
            const quantity = Number(row.querySelector('[name$="[quantity]"]').value) || 0;
            const price = Number(row.querySelector('[name$="[unit_price]"]').value) || 0;
            const taxPercent = Number(row.querySelector('[name$="[tax_percent]"]').value) || 0;
            const rowDiscount = Number(row.querySelector('[name$="[discount_amount]"]').value) || 0;
            const rowSubtotal = quantity * price;
            const rowTax = rowSubtotal * taxPercent / 100;
            const rowTotal = Math.max(rowSubtotal + rowTax - rowDiscount, 0);
            subtotal += rowSubtotal;
            tax += rowTax;
            discount += rowDiscount;
            grandTotal += rowTotal;
            row.querySelector('.line-total').textContent = rowTotal.toFixed(2);
        });

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalTax').textContent = tax.toFixed(2);
        document.getElementById('totalDiscount').textContent = discount.toFixed(2);
        document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    }

    document.getElementById('addBillItem').addEventListener('click', addRow);
    tbody.addEventListener('input', updateTotals);
    tbody.addEventListener('change', (event) => {
        if (event.target.matches('[name$="[product_id]"]')) {
            const product = productOptions.find((item) => String(item.id) === event.target.value);
            if (product) {
                event.target.closest('tr').querySelector('[name$="[unit_price]"]').value = product.price;
            }
        }
        updateTotals();
    });
    tbody.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-bill-item');
        if (removeButton && tbody.querySelectorAll('tr').length > 1) {
            removeButton.closest('tr').remove();
            updateTotals();
        }
    });
    updateTotals();
    @endif
})();
</script>
@endpush
