<tr class="jewellery-item border-b border-gray-100 dark:border-zinc-800">
    <td class="p-2">
        <select name="items[{{ $index }}][product_id]" required class="product-select min-w-52 w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
            <option value="">Select product</option>
            @foreach ($products as $product)
                <option value="{{ $product->product_id }}"
                    data-touch="{{ $product->touch }}"
                    data-wastage="{{ $product->default_wastage }}"
                    @selected(old("items.$index.product_id", $item->product_id ?? '') == $product->product_id)>
                    {{ $product->product_name }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="p-2"><input name="items[{{ $index }}][pcs]" type="number" min="1" step="1" required value="{{ old("items.$index.pcs", $item->pcs ?? $item->quantity ?? 1) }}" class="pcs-value w-20 rounded-lg border border-gray-200 bg-white px-2 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"></td>
    <td class="p-2"><input name="items[{{ $index }}][gross_weight]" type="number" min="0" step="0.001" value="{{ old("items.$index.gross_weight", $item->gross_weight ?? '') }}" class="w-24 rounded-lg border border-gray-200 bg-white px-2 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"></td>
    <td class="p-2"><input name="items[{{ $index }}][net_weight]" type="number" min="0" step="0.001" value="{{ old("items.$index.net_weight", $item->net_weight ?? '') }}" class="net-weight w-24 rounded-lg border border-gray-200 bg-white px-2 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"></td>
    <td class="p-2"><input name="items[{{ $index }}][touch]" type="number" min="0" max="100" step="0.001" value="{{ old("items.$index.touch", $item->touch ?? $item->product_touch ?? '') }}" class="touch-value w-24 rounded-lg border border-gray-200 bg-white px-2 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"></td>
    <td class="p-2"><input name="items[{{ $index }}][wastage]" type="number" min="0" max="100" step="0.001" value="{{ old("items.$index.wastage", $item->wastage ?? $item->product_default_wastage ?? '') }}" class="wastage-value w-24 rounded-lg border border-gray-200 bg-white px-2 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"></td>
    <td class="p-2"><input type="number" readonly value="{{ $item->fine_weight ?? '' }}" class="fine-weight w-24 rounded-lg border border-gray-200 bg-gray-100 px-2 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"></td>
    <td class="p-2 text-center"><button type="button" class="remove-jewellery-item rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30" aria-label="Remove item">✕</button></td>
</tr>
