@extends('layouts.app')

@section('title', 'Jewellery Vouchers')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Jewellery Vouchers'])
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Jewellery billing</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Vouchers</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track party vouchers, item weights, fine weight and outstanding gold balances.</p>
                    </div>
                    <a href="{{ route('invoice.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">＋ Create voucher</a>
                </div>

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-zinc-800">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Recent vouchers</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a voucher to inspect, print or edit it.</p>
                        </div>
                        <input type="search" id="voucherSearch" placeholder="Search vouchers..." class="w-56 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-zinc-800/60 dark:text-gray-400">
                                <tr><th class="px-6 py-4">Voucher</th><th class="px-6 py-4">Party</th><th class="px-6 py-4">Date</th><th class="px-6 py-4 text-right">Fine weight</th><th class="px-6 py-4 text-center">Actions</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @forelse ($invoices as $invoice)
                                    <tr class="voucher-row hover:bg-amber-50/50 dark:hover:bg-amber-950/10">
                                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}<span class="mt-1 block text-xs font-normal text-gray-500">ID {{ $invoice->invoice_id }}</span></td>
                                        <td class="px-6 py-4">{{ $invoice->customer_name ?? 'Customer unavailable' }}</td>
                                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-amber-700 dark:text-amber-300">{{ number_format((float) $invoice->total_fine, 3) }} g</td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('invoice.view', $invoice->invoice_id) }}" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-zinc-800" aria-label="View voucher">View</a>
                                                <a href="{{ route('invoice.edit', $invoice->invoice_id) }}" class="rounded-lg p-2 text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-950/30" aria-label="Edit voucher">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-16 text-center">
                                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-2xl text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">◇</div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">No vouchers yet</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a voucher to record a jewellery sale.</p>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($invoices->hasPages())
                        <div class="border-t border-gray-200 px-6 py-4 dark:border-zinc-800">{{ $invoices->links() }}</div>
                    @endif
                </section>
            </main>
        </div>
    </div>
    <script>
        document.getElementById('voucherSearch').addEventListener('input', event => {
            const query = event.target.value.toLowerCase().trim();
            document.querySelectorAll('.voucher-row').forEach(row => {
                row.hidden = !row.textContent.toLowerCase().includes(query);
            });
        });
    </script>
@endsection
