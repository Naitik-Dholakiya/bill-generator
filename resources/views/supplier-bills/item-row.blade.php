<tr>
    <td class="px-4 py-3">
        <label for="product-{{ $index }}" class="sr-only">Product</label>
        <select id="product-{{ $index }}" name="items[{{ $index }}][product_id]" required
            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
            <option value="">Select product</option>
            @foreach ($products as $product)
                <option value="{{ $product->product_id }}" @selected(($row->product_id ?? '') == $product->product_id)>
                    {{ $product->product_code }} - {{ $product->product_name }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="px-4 py-3">
        <label for="quantity-{{ $index }}" class="sr-only">Quantity</label>
        <input id="quantity-{{ $index }}" type="number" name="items[{{ $index }}][quantity]" min="1" max="2147483647" step="1" required
            value="{{ $row->quantity ?? 1 }}" class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800">
    </td>
    <td class="px-4 py-3">
        <label for="unit-price-{{ $index }}" class="sr-only">Unit cost</label>
        <input id="unit-price-{{ $index }}" type="number" name="items[{{ $index }}][unit_price]" min="0" step="0.01" required
            value="{{ $row->unit_price ?? '' }}" class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800">
    </td>
    <td class="px-4 py-3">
        <label for="tax-{{ $index }}" class="sr-only">Tax percent</label>
        <input id="tax-{{ $index }}" type="number" name="items[{{ $index }}][tax_percent]" min="0" max="100" step="0.01"
            value="{{ $row->tax_percent ?? 0 }}" class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800">
    </td>
    <td class="px-4 py-3">
        <label for="discount-{{ $index }}" class="sr-only">Discount amount</label>
        <input id="discount-{{ $index }}" type="number" name="items[{{ $index }}][discount_amount]" min="0" step="0.01"
            value="{{ $row->discount_amount ?? 0 }}" class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-right dark:border-zinc-700 dark:bg-zinc-800">
    </td>
    <td class="line-total px-4 py-3 text-right font-medium">0.00</td>
    <td class="px-4 py-3 text-right">
        <button type="button" class="remove-bill-item rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40" aria-label="Remove item">
            <i class="ti ti-trash" aria-hidden="true"></i>
        </button>
    </td>
</tr>
