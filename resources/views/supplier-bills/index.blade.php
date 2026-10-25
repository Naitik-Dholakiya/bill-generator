@extends('layouts.app')

@section('title', 'Supplier Bills')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Supplier Bills'])
            <main class="flex-1 overflow-y-auto p-6">
                @if ($errors->has('stock'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ $errors->first('stock') }}</div>
                @endif
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ session('error') }}</div>
                @endif
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Supplier Bills</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage purchases and amounts payable to suppliers.</p>
                    </div>
                    <a href="{{ route('supplier-bills.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 font-medium text-white hover:bg-cyan-700">
                        <i class="ti ti-plus" aria-hidden="true"></i>Create Supplier Bill
                    </a>
                </div>

                <form method="GET" action="{{ route('supplier-bills.index') }}" class="mb-6 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row">
                    <label class="sr-only" for="bill-search">Search bills</label>
                    <input id="bill-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search bill number or supplier..."
                        class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                    <label class="sr-only" for="bill-status">Filter by payment status</label>
                    <select id="bill-status" name="payment_status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                        <option value="">All payment statuses</option>
                        @foreach (['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl border border-gray-300 px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800">Filter</button>
                </form>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/70">
                                <tr>
                                    <th class="px-5 py-4 text-left font-semibold">Bill Number</th>
                                    <th class="px-5 py-4 text-left font-semibold">Supplier</th>
                                    <th class="px-5 py-4 text-left font-semibold">Bill Date</th>
                                    <th class="px-5 py-4 text-right font-semibold">Total</th>
                                    <th class="px-5 py-4 text-center font-semibold">Payment</th>
                                    <th class="px-5 py-4 text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @forelse ($bills as $bill)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-5 py-4">
                                            <a href="{{ route('supplier-bills.show', $bill->purchase_id) }}" class="font-semibold text-cyan-700 hover:underline dark:text-cyan-400">{{ $bill->purchase_number }}</a>
                                        </td>
                                        <td class="px-5 py-4 font-medium">{{ $bill->supplier_name }}</td>
                                        <td class="px-5 py-4">{{ \Carbon\Carbon::parse($bill->purchase_date)->format('d M Y') }}</td>
                                        <td class="px-5 py-4 text-right font-semibold">₹{{ number_format($bill->grand_total, 2) }}</td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $bill->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : ($bill->payment_status === 'partial' ? 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300') }}">
                                                {{ ucfirst($bill->payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('supplier-bills.show', $bill->purchase_id) }}" class="rounded-lg p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40" aria-label="View bill"><i class="ti ti-eye"></i></a>
                                                <a href="{{ route('supplier-bills.edit', $bill->purchase_id) }}" class="rounded-lg p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40" aria-label="Edit bill"><i class="ti ti-edit"></i></a>
                                                <form action="{{ route('supplier-bills.destroy', $bill->purchase_id) }}" method="POST" onsubmit="return confirm('Delete this bill and reverse its stock quantities?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40" aria-label="Delete bill"><i class="ti ti-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-14 text-center text-gray-500 dark:text-gray-400">No supplier bills yet. Create your first bill to record a purchase.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($bills->hasPages())
                        <div class="border-t border-gray-200 px-5 py-4 dark:border-zinc-800">{{ $bills->links() }}</div>
                    @endif
                </div>
            </main>
            @include('layout.footer')
        </div>
    </div>
@endsection
