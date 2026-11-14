@if ($revisions->isNotEmpty())
    <details class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <summary class="cursor-pointer px-6 py-5 font-semibold text-gray-900 dark:text-white">
            Saved invoice history ({{ $revisions->count() }} previous version{{ $revisions->count() === 1 ? '' : 's' }})
        </summary>
        <div class="divide-y divide-gray-100 border-t border-gray-200 dark:divide-zinc-800 dark:border-zinc-800">
            @foreach ($revisions as $revision)
                @php
                    $previousInvoice = $revision->snapshot['invoice'] ?? [];
                    $previousItems = $revision->snapshot['items'] ?? [];
                @endphp
                <div class="flex flex-col gap-2 px-6 py-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            Revision {{ $revision->revision_number }}
                            · {{ $previousInvoice['customer_name_snapshot'] ?? 'Customer' }}
                        </p>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($revision->created_at)->format('d M Y, h:i A') }}
                            · {{ count($previousItems) }} item{{ count($previousItems) === 1 ? '' : 's' }}
                        </p>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ !empty($previousInvoice['grand_total']) ? '₹'.number_format((float) $previousInvoice['grand_total'], 2) : 'Weight-only voucher' }}
                    </p>
                </div>
            @endforeach
        </div>
    </details>
@endif
