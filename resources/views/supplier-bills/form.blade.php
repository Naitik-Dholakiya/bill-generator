@php
    $today = now()->toDateString();
    $productOptions = $products->map(fn ($product) => [
        'id' => $product->product_id,
        'label' => $product->product_code.' - '.$product->product_name,
        'price' => $product->purchase_price,
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
            <div class="md:col-span-2 xl:col-span-4">
                <label for="bill-notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea id="bill-notes" name="notes" rows="2" maxlength="2000"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">{{ old('notes', $bill->notes ?? '') }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

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
</form>

@push('scripts')
<script>
(() => {
    const tbody = document.getElementById('billItems');
    const template = document.getElementById('billItemTemplate');
    const productOptions = @json($productOptions);
    let nextIndex = {{ count($rows) }};

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
})();
</script>
@endpush
