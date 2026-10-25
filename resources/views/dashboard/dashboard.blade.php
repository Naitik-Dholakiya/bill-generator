@extends('layouts.app')

@section('content')

@php
    // Null-safe formatters. A missing key => null => "No data" in the markup.
    // A present key (even 0) is formatted and shown as a real value.
    $money = fn ($v) => is_null($v) ? null : '₹' . number_format($v);
    $num   = fn ($v) => is_null($v) ? null : number_format($v);
    $pct   = fn ($v) => is_null($v) ? null : number_format($v, 1) . '%';

    $custCount  = $kpis['customers_total']     ?? null;
    $suppCount  = $kpis['suppliers_active']    ?? null;
    $prodCount  = $kpis['products_total']      ?? null;
    $invCount   = $kpis['invoices_total']       ?? null;

    $setupSteps = [
        [
            'title' => 'Add your first customer',
            'description' => 'Save a customer to bill.',
            'icon' => 'ti-users',
            'url' => route('createCustomer'),
            'complete' => ($custCount ?? 0) > 0,
        ],
        [
            'title' => 'Add a supplier',
            'description' => 'Keep supplier details handy.',
            'icon' => 'ti-truck-delivery',
            'url' => route('createSupplier'),
            'complete' => ($suppCount ?? 0) > 0,
        ],
        [
            'title' => 'Set up your products',
            'description' => 'Add items you sell.',
            'icon' => 'ti-package',
            'url' => route('products.create'),
            'complete' => ($prodCount ?? 0) > 0,
        ],
        [
            'title' => 'Create your first invoice',
            'description' => 'Start billing your customers.',
            'icon' => 'ti-file-invoice',
            'url' => route('invoice.create'),
            'complete' => ($invCount ?? 0) > 0,
        ],
    ];
    $completedSetupSteps = collect($setupSteps)->where('complete', true)->count();
    $setupProgress = (int) round(($completedSetupSteps / count($setupSteps)) * 100);

    $collectedTotal = $kpis['collected_total'] ?? null;
    $revenueTotalRaw = $kpis['revenue_total'] ?? null;
    $collectionRate = (!is_null($collectedTotal) && !is_null($revenueTotalRaw) && $revenueTotalRaw > 0)
        ? round($collectedTotal / $revenueTotalRaw * 100, 1)
        : null;
@endphp

<div class="flex h-screen overflow-hidden bg-slate-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        @include('layout.navbar')

        <main class="flex-1 space-y-6 overflow-y-auto p-4 sm:p-6 lg:p-8">

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">
                            Business overview <span class="px-1 text-slate-300 dark:text-zinc-600">/</span> {{ now()->format('l, d M Y') }}
                        </p>
                        <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                            Welcome back, {{ request()->cookie('CSGO') ?? 'Admin' }}
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-zinc-400">
                            Keep your day moving with a clear view of your billing, customers, products, and suppliers.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium text-slate-500 dark:text-zinc-400">
                            <span><i class="ti ti-users mr-1 text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>{{ $num($custCount) ?? '0' }} customers</span>
                            <span><i class="ti ti-package mr-1 text-violet-600 dark:text-violet-400" aria-hidden="true"></i>{{ $num($prodCount) ?? '0' }} products</span>
                            <span><i class="ti ti-truck-delivery mr-1 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>{{ $num($suppCount) ?? '0' }} suppliers</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-3">
                        <a href="{{ route('products.create') }}"
                           class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 dark:focus:ring-zinc-700">
                            <i class="ti ti-package text-base" aria-hidden="true"></i> Add product
                        </a>
                        <a href="{{ route('invoice.create') }}"
                           class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-cyan-700 px-4 text-sm font-semibold text-white shadow-sm shadow-cyan-900/10 transition hover:bg-cyan-800 focus:outline-none focus:ring-4 focus:ring-cyan-500/25 dark:bg-cyan-600 dark:hover:bg-cyan-500">
                            <i class="ti ti-plus text-base" aria-hidden="true"></i> Create invoice
                        </a>
                    </div>
                </div>
            </section>

            @if($completedSetupSteps < count($setupSteps))
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7" aria-labelledby="setup-guide-title">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">Getting started</p>
                            <h3 id="setup-guide-title" class="mt-1 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">Get your business set up</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Follow these steps to get your workspace ready for everyday billing.</p>
                        </div>
                        <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $completedSetupSteps }} of {{ count($setupSteps) }} complete
                        </span>
                    </div>
                    <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800" role="progressbar" aria-label="Workspace setup progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $setupProgress }}">
                        <div class="h-full rounded-full bg-cyan-600 transition-[width] duration-500 dark:bg-cyan-500" style="width: {{ $setupProgress }}%"></div>
                    </div>
                    <ol class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($setupSteps as $index => $step)
                            <li>
                                @if ($step['complete'])
                                    <div class="flex h-full items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                                            <i class="ti ti-check text-lg" aria-hidden="true"></i>
                                        </span>
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-800 dark:text-zinc-100">{{ $step['title'] }}</span>
                                            <span class="mt-1 block text-xs text-emerald-700 dark:text-emerald-400">Completed</span>
                                        </span>
                                    </div>
                                @else
                                    <a href="{{ $step['url'] }}" class="group flex h-full items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-cyan-300 hover:bg-cyan-50/40 focus:outline-none focus:ring-4 focus:ring-cyan-500/15 dark:border-zinc-700 dark:hover:border-cyan-800 dark:hover:bg-cyan-950/20">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            <i class="ti {{ $step['icon'] }} text-lg" aria-hidden="true"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Step {{ $index + 1 }}</span>
                                            <span class="mt-0.5 block text-sm font-semibold text-slate-800 group-hover:text-cyan-800 dark:text-zinc-100 dark:group-hover:text-cyan-300">{{ $step['title'] }}</span>
                                            <span class="mt-1 block text-xs text-slate-500 dark:text-zinc-400">{{ $step['description'] }}</span>
                                        </span>
                                        <i class="ti ti-arrow-up-right ml-auto shrink-0 text-sm text-slate-400 transition group-hover:text-cyan-700 dark:group-hover:text-cyan-300" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- ============================== KPI grid ============================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Revenue --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Revenue (billed)</p>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <i class="ti ti-currency-rupee text-sm text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($money($kpis['revenue_total'] ?? null)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs text-gray-400 mt-2">Create an invoice to start tracking revenue</p>
                    @else
                        <p class="text-2xl font-medium">{{ $money($kpis['revenue_total']) }}</p>
                        <p class="text-xs mt-2 flex items-center gap-1
                                  {{ ($kpis['revenue_growth'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            @if(!is_null($kpis['revenue_growth'] ?? null))
                                <i class="ti ti-trending-up text-sm" aria-hidden="true"></i>
                                {{ $pct($kpis['revenue_growth']) }} <span class="text-gray-400">vs last month</span>
                            @else
                                <span class="text-gray-400">vs last month: no data</span>
                            @endif
                        </p>
                    @endif
                </div>

                {{-- Outstanding (pending + partial) --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Outstanding</p>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="ti ti-clock-dollar text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($money($kpis['outstanding_total'] ?? null)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs text-gray-400 mt-2">No unpaid invoices tracked yet</p>
                    @else
                        <p class="text-2xl font-medium">{{ $money($kpis['outstanding_total']) }}</p>
                        <p class="text-xs text-gray-400 mt-2">
                            across {{ $num($kpis['outstanding_count'] ?? 0) }} unpaid invoice{{ ($kpis['outstanding_count'] ?? 0) == 1 ? '' : 's' }}
                        </p>
                    @endif
                </div>

                {{-- Aged / at-risk (proxy for overdue — see note at top) --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending 30+ days</p>
                        <span class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                            <i class="ti ti-alert-triangle text-sm text-red-600 dark:text-red-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($money($kpis['aged_pending_total'] ?? null)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs text-gray-400 mt-2">Proxy metric — no due_date column yet</p>
                    @else
                        <p class="text-2xl font-medium">{{ $money($kpis['aged_pending_total']) }}</p>
                        <p class="text-xs text-gray-400 mt-2">
                            {{ $num($kpis['aged_pending_count'] ?? 0) }} invoice{{ ($kpis['aged_pending_count'] ?? 0) == 1 ? '' : 's' }} aging past 30 days
                        </p>
                    @endif
                </div>

                {{-- GST collected --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tax collected</p>
                        <span class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center">
                            <i class="ti ti-receipt-tax text-sm text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($money($kpis['gst_collected'] ?? null)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs text-gray-400 mt-2">No taxed invoices yet</p>
                    @else
                        <p class="text-2xl font-medium">{{ $money($kpis['gst_collected']) }}</p>
                        <p class="text-xs text-gray-400 mt-2">this month, from total_tax on invoices</p>
                    @endif
                </div>

                {{-- Customers --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Customers</p>
                        <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                            <i class="ti ti-users text-sm text-purple-600 dark:text-purple-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($num($custCount)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs mt-2"><a href="{{ route('createCustomer') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline">Add your first customer →</a></p>
                    @else
                        <p class="text-2xl font-medium">{{ $num($custCount) }}</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                            +{{ $num($kpis['customers_new'] ?? 0) }} new this month
                        </p>
                    @endif
                </div>

                {{-- Products --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Active products</p>
                        <span class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center">
                            <i class="ti ti-package text-sm text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($num($prodCount)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs mt-2"><a href="{{ route('products.create') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline">Add your first product →</a></p>
                    @else
                        <p class="text-2xl font-medium">{{ $num($prodCount) }}</p>
                        <p class="text-xs text-gray-400 mt-2">across {{ $num($kpis['categories_total'] ?? null) ?? 'no' }} categories</p>
                    @endif
                </div>

                {{-- Reorder watch --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Reorder watch</p>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="ti ti-alert-circle text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($num($kpis['reorder_watch_count'] ?? null)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs text-gray-400 mt-2">Set a reorder level on a product to track this</p>
                    @else
                        <p class="text-2xl font-medium">{{ $num($kpis['reorder_watch_count']) }}</p>
                        <p class="text-xs text-gray-400 mt-2">products at or below reorder level</p>
                    @endif
                </div>

                {{-- Suppliers --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Active suppliers</p>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <i class="ti ti-truck-delivery text-sm text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if(is_null($num($suppCount)))
                        <p class="text-2xl font-medium text-gray-300 dark:text-zinc-700">No data</p>
                        <p class="text-xs mt-2"><a href="{{ route('createSupplier') }}" class="text-cyan-600 dark:text-cyan-400 hover:underline">Add your first supplier →</a></p>
                    @else
                        <p class="text-2xl font-medium">{{ $num($suppCount) }}</p>
                        <p class="text-xs text-gray-400 mt-2">
                            avg invoice {{ $money($kpis['avg_invoice_value'] ?? null) ?? 'no data' }}
                        </p>
                    @endif
                </div>

            </div>

            {{-- ============================== Revenue trend + Invoice status ============================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Revenue trend (2/3 width) --}}
                <div class="lg:col-span-2 bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-medium text-sm">Revenue trend</h3>
                        <select class="text-xs bg-gray-100 dark:bg-zinc-800 border-0 rounded-lg px-3 py-1.5
                                       text-gray-600 dark:text-gray-300 outline-none cursor-pointer" aria-label="Period">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="h-56 relative">
                        <canvas id="revenueChart"></canvas>
                        <div id="revenueEmptyState"
                             class="hidden absolute inset-0 flex flex-col items-center justify-center text-center gap-1">
                            <i class="ti ti-chart-line text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                            <p class="text-sm text-gray-400">No invoices in this period yet</p>
                        </div>
                    </div>
                </div>

                {{-- Invoice status breakdown --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <h3 class="font-medium text-sm mb-5">Invoice status</h3>
                    @if(empty($invoiceStatus) || (($invoiceStatus['paid'] ?? 0) + ($invoiceStatus['partial'] ?? 0) + ($invoiceStatus['pending'] ?? 0)) === 0)
                        <div class="h-40 flex flex-col items-center justify-center text-center gap-1">
                            <i class="ti ti-file-invoice text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                            <p class="text-sm text-gray-400">No invoices yet</p>
                        </div>
                    @else
                        <div class="h-40 flex items-center justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    @endif
                    <div class="space-y-2.5 mt-5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Paid
                            </span>
                            <span class="font-medium">{{ $num($invoiceStatus['paid'] ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Partial
                            </span>
                            <span class="font-medium">{{ $num($invoiceStatus['partial'] ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Pending
                            </span>
                            <span class="font-medium">{{ $num($invoiceStatus['pending'] ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-2 mt-1 border-t border-gray-50 dark:border-zinc-800">
                            <span class="text-gray-500 dark:text-gray-400">Collection rate</span>
                            <span class="font-medium">{{ !is_null($collectionRate) ? $collectionRate.'%' : 'No data' }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================== Top customers + Top products ============================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Top customers --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="font-medium text-sm">Top customers</h3>
                        <a href="{{ route('customers.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline">View all</a>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                        @forelse ($topCustomers as $c)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-r from-cyan-500 to-purple-600 flex items-center justify-center
                                            text-white text-xs font-medium flex-shrink-0">
                                    {{ strtoupper(substr($c->customer_name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate">{{ $c->customer_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $c->customer_code }} · {{ $c->invoice_count }} invoice{{ $c->invoice_count == 1 ? '' : 's' }}</p>
                                </div>
                                <p class="text-sm font-medium flex-shrink-0">₹{{ number_format($c->total_spent) }}</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center">
                                <i class="ti ti-users text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                                <p class="text-sm text-gray-400 mt-2">No customers yet</p>
                                <a href="{{ route('createCustomer') }}"
                                   class="inline-flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400 hover:underline mt-1">
                                    Add a customer <i class="ti ti-arrow-right text-xs" aria-hidden="true"></i>
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Top products --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="font-medium text-sm">Top products</h3>
                        <a href="{{ route('products.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline">View all</a>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                        @forelse ($topProducts as $p)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <span class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                    <i class="ti ti-package text-sm text-purple-600 dark:text-purple-400" aria-hidden="true"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate">{{ $p->product_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $p->category_name }} · {{ $p->qty_sold }} sold</p>
                                </div>
                                <p class="text-sm font-medium flex-shrink-0">₹{{ number_format($p->revenue) }}</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center">
                                <i class="ti ti-package text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                                <p class="text-sm text-gray-400 mt-2">No products yet</p>
                                <a href="{{ route('products.create') }}"
                                   class="inline-flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400 hover:underline mt-1">
                                    Add a product <i class="ti ti-arrow-right text-xs" aria-hidden="true"></i>
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ============================== Category sales + Reorder watch ============================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Category-wise sales --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <h3 class="font-medium text-sm mb-4">Sales by category</h3>
                    <div class="space-y-4">
                        @forelse ($categorySales as $cat)
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="text-gray-600 dark:text-gray-300">{{ $cat->category_name }}</span>
                                    <span class="text-gray-400">₹{{ number_format($cat->revenue) }} · {{ $cat->percent }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-zinc-800 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-purple-600"
                                         style="width: {{ $cat->percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <i class="ti ti-chart-donut text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                                <p class="text-sm text-gray-400 mt-2">No category sales yet</p>
                                <p class="text-xs text-gray-400 mt-1">Shows up once an invoice includes a categorized product</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Reorder watch --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="font-medium text-sm">Reorder watch</h3>
                        <a href="{{ route('stock.index') }}" class="text-xs text-cyan-600 hover:underline dark:text-cyan-400">Manage stock</a>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                        @forelse ($reorderWatch as $r)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <span class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                    <i class="ti ti-alert-triangle text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate">{{ $r->product_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $r->product_code }} · supplier: {{ $r->supplier_name }}</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">{{ number_format($r->quantity_in_stock) }} / {{ number_format($r->reorder_level) }} in stock</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center">
                                <i class="ti ti-list-check text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                                <p class="text-sm text-gray-400 mt-2">Nothing to watch</p>
                                <p class="text-xs text-gray-400 mt-1">Set a reorder level on a product to see it here</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ============================== Recent invoices ============================== --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                    <h3 class="font-medium text-sm">Recent invoices</h3>
                    <div class="flex items-center gap-2">
                        <select class="text-xs bg-gray-100 dark:bg-zinc-800 border-0 rounded-lg px-3 py-1.5
                                       text-gray-600 dark:text-gray-300 outline-none cursor-pointer" aria-label="Filter status">
                            <option value="">All status</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="pending">Pending</option>
                        </select>
                        <a href="{{ route('invoice.index') }}"
                           class="text-xs text-cyan-600 dark:text-cyan-400 border border-gray-200 dark:border-zinc-700
                                  px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                            View all →
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-zinc-800">
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Invoice #</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Customer</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Date</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Tax</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Discount</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Total</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Status</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-zinc-800/50"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-zinc-800">
                            @forelse ($recentInvoices as $inv)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-cyan-600 dark:text-cyan-400">{{ $inv->invoice_number }}</td>
                                    <td class="px-6 py-3.5">{{ $inv->customer_name }}</td>
                                    <td class="px-6 py-3.5 text-gray-400">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}</td>
                                    <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400">₹{{ number_format($inv->total_tax) }}</td>
                                    <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400">₹{{ number_format($inv->discount_amount) }}</td>
                                    <td class="px-6 py-3.5 font-medium">₹{{ number_format($inv->grand_total) }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($inv->payment_status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                        @elseif($inv->payment_status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">Partial</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('invoice.pdf', $inv->invoice_id) }}"
                                           class="text-xs text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                            <i class="ti ti-chevron-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center">
                                        <i class="ti ti-file-invoice text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                                        <p class="text-sm text-gray-400 mt-2">No invoices yet</p>
                                        <a href="{{ route('invoice.create') }}"
                                           class="inline-flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400 hover:underline mt-1">
                                            Create your first invoice <i class="ti ti-arrow-right text-xs" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================== Supplier snapshot ============================== --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                    <h3 class="font-medium text-sm">Supplier snapshot</h3>
                    <a href="{{ route('suppliers.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline">View all</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
                    @forelse ($supplierSnapshot as $s)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-zinc-800">
                            <span class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                                <i class="ti ti-truck-delivery text-sm text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate">{{ $s->supplier_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $s->product_count }} product{{ $s->product_count == 1 ? '' : 's' }} supplied</p>
                            </div>
                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $s->status === '1' ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                  title="{{ $s->status === '1' ? 'Active' : 'Inactive' }}"></span>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8">
                            <i class="ti ti-truck-delivery text-2xl text-gray-300 dark:text-zinc-700" aria-hidden="true"></i>
                            <p class="text-sm text-gray-400 mt-2">No suppliers yet</p>
                            <a href="{{ route('createSupplier') }}"
                               class="inline-flex items-center gap-1 text-xs text-cyan-600 dark:text-cyan-400 hover:underline mt-1">
                                Add a supplier <i class="ti ti-arrow-right text-xs" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

        </main>

        @include('layout.footer')

    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
function setTheme(theme) {
    const root = document.documentElement;
    const icon = document.getElementById('themeIcon');
    if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
    const icons = { light: 'ti-sun', dark: 'ti-moon', system: 'ti-device-laptop' };
    if (icon) icon.className = `ti ${icons[theme]} text-base`;
}

// Restore theme on load
(function () {
    const saved = localStorage.getItem('theme') || 'system';
    setTheme(saved);
})();

// ---- Charts -------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
    const textColor = isDark ? '#9ca3af' : '#6b7280';

    const revenueLabels = {!! json_encode($revenueTrend['labels'] ?? []) !!};
    const revenueValues = {!! json_encode($revenueTrend['values'] ?? []) !!};
    const hasRevenueData = revenueValues.length > 0 && revenueValues.some(v => Number(v) !== 0);

    if (hasRevenueData) {
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue',
                    data: revenueValues,
                    borderColor: '#06b6d4',
                    backgroundColor: function (ctx) {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                        g.addColorStop(0, 'rgba(6,182,212,0.25)');
                        g.addColorStop(1, 'rgba(147,51,234,0.02)');
                        return g;
                    },
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#a855f7',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => '₹' + v.toLocaleString('en-IN') } }
                }
            }
        });
    } else {
        document.getElementById('revenueChart').classList.add('hidden');
        const empty = document.getElementById('revenueEmptyState');
        if (empty) empty.classList.remove('hidden');
    }

    const statusData = [
        {{ $invoiceStatus['paid'] ?? 0 }},
        {{ $invoiceStatus['partial'] ?? 0 }},
        {{ $invoiceStatus['pending'] ?? 0 }}
    ];
    const hasStatusData = statusData.some(v => v !== 0);

    if (hasStatusData && document.getElementById('statusChart')) {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Partial', 'Pending'],
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#10b981', '#06b6d4', '#f59e0b'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
@endpush

@endsection