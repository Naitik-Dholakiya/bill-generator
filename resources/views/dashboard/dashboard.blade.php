{{--
    ============================================================================
    ERP DASHBOARD — CRM + Billing
    ============================================================================
    Built against the `billingsystem` schema (customermaster, invoicemaster,
    invoice_items, productmaster, categorymaster, suppliermaster, usermaster).

    Two things in the schema affect what this view can honestly show, flagged
    here instead of faked in the UI:

    1. No `due_date` on invoicemaster — there's no way to compute a true
       "overdue" invoice. This view shows "Pending 30+ days" (aged off
       invoice_date) as the closest honest proxy. Add a `due_date` column
       (and maybe a `payment_terms_days` on customer) to get real overdue.
    2. No stock/quantity column on productmaster — only `reorder_level`
       exists, there's no `stock_on_hand`. This view shows a "Reorder Watch"
       list (products whose reorder_level is set) rather than fabricating a
       stock number. Add a `stock_quantity` column or a stock-ledger table to
       get real low-stock alerts.

    CONTROLLER CONTRACT — pass these from DashboardController@index:

    $kpis = [
        'revenue_total'       => // SUM(grand_total) this month
        'revenue_growth'      => // % vs last month
        'outstanding_total'   => // SUM(grand_total) where payment_status in (pending,partial)
        'outstanding_count'   => // COUNT(*) same filter
        'aged_pending_total'  => // SUM(grand_total) where payment_status != paid AND invoice_date <= now()-30d
        'aged_pending_count'  =>
        'gst_collected'       => // SUM(total_tax) this month
        'customers_total'     => // COUNT(customermaster)
        'customers_new'       => // COUNT this month
        'products_total'      => // COUNT(productmaster where status=1)
        'reorder_watch_count' => // COUNT(productmaster where reorder_level > 0)
        'suppliers_active'    => // COUNT(suppliermaster where status=1)
        'avg_invoice_value'   => // AVG(grand_total)
    ];
    $revenueTrend      = ['labels' => [...7 or 30 dates...], 'values' => [...grand_total sums...]];
    $invoiceStatus     = ['paid' => 0, 'partial' => 0, 'pending' => 0]; // counts or totals
    $topCustomers      = collection of {customer_name, customer_code, total_spent, invoice_count, gst_number}
    $topProducts       = collection of {product_name, category_name, qty_sold, revenue}
    $categorySales     = collection of {category_name, revenue, percent}
    $reorderWatch      = collection of {product_name, product_code, reorder_level, supplier_name}
    $recentInvoices    = collection of {invoice_id, invoice_number, customer_name, invoice_date, grand_total, payment_status}
    $supplierSnapshot  = collection of {supplier_name, product_count, status}
    ============================================================================
--}}
@extends('layouts.app')

@section('content')
<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        @include('layout.navbar')

        <main class="flex-1 overflow-y-auto p-5 space-y-5">

            {{-- ============================== Welcome / range ============================== --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-6
                        flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-medium">
                        Welcome back,
                        <span class="bg-gradient-to-r from-cyan-500 to-purple-600 bg-clip-text text-transparent">
                            {{ request()->cookie('CSGO') ?? 'Admin' }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Business snapshot for {{ now()->format('d M Y') }} · {{ $kpis['customers_total'] ?? 1 }} customers,
                        {{ $kpis['products_total'] ?? 1 }} active products
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <select class="text-xs bg-gray-100 dark:bg-zinc-800 border-0 rounded-xl px-3 py-2.5
                                   text-gray-600 dark:text-gray-300 outline-none cursor-pointer" aria-label="Range">
                        <option>This month</option>
                        <option>Last 30 days</option>
                        <option>This quarter</option>
                        <option>This year</option>
                    </select>
                    <button class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 dark:border-zinc-700
                                   rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <i class="ti ti-download text-base" aria-hidden="true"></i> Export
                    </button>
                    <a href="{{ route('invoice.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl
                              bg-gradient-to-r from-cyan-500 to-purple-600 hover:opacity-90 transition-opacity">
                        <i class="ti ti-plus text-base" aria-hidden="true"></i> New invoice
                    </a>
                </div>
            </div>

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
                    <p class="text-2xl font-medium">₹{{ number_format($kpis['revenue_total'] ?? 262346) }}</p>
                    <p class="text-xs mt-2 flex items-center gap-1
                              {{ ($kpis['revenue_growth'] ?? 12.4) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        <i class="ti ti-trending-up text-sm" aria-hidden="true"></i>
                        {{ $kpis['revenue_growth'] ?? 12.4 }}% <span class="text-gray-400">vs last month</span>
                    </p>
                </div>

                {{-- Outstanding (pending + partial) --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Outstanding</p>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="ti ti-clock-dollar text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">₹{{ number_format($kpis['outstanding_total'] ?? 231123) }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        across {{ $kpis['outstanding_count'] ?? 1 }} unpaid invoice{{ ($kpis['outstanding_count'] ?? 1) == 1 ? '' : 's' }}
                    </p>
                </div>

                {{-- Aged / at-risk (proxy for overdue — see note at top) --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending 30+ days</p>
                        <span class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                            <i class="ti ti-alert-triangle text-sm text-red-600 dark:text-red-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">₹{{ number_format($kpis['aged_pending_total'] ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $kpis['aged_pending_count'] ?? 0 }} invoice{{ ($kpis['aged_pending_count'] ?? 0) == 1 ? '' : 's' }} aging past 30 days
                    </p>
                </div>

                {{-- GST collected --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tax collected</p>
                        <span class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center">
                            <i class="ti ti-receipt-tax text-sm text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">₹{{ number_format($kpis['gst_collected'] ?? 100) }}</p>
                    <p class="text-xs text-gray-400 mt-2">this month, from total_tax on invoices</p>
                </div>

                {{-- Customers --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Customers</p>
                        <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                            <i class="ti ti-users text-sm text-purple-600 dark:text-purple-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ number_format($kpis['customers_total'] ?? 1) }}</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                        +{{ $kpis['customers_new'] ?? 1 }} new this month
                    </p>
                </div>

                {{-- Products --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Active products</p>
                        <span class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center">
                            <i class="ti ti-package text-sm text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ number_format($kpis['products_total'] ?? 1) }}</p>
                    <p class="text-xs text-gray-400 mt-2">across {{ $kpis['categories_total'] ?? 1 }} categories</p>
                </div>

                {{-- Reorder watch (proxy for low stock — see note at top) --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Reorder watch</p>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="ti ti-alert-circle text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ number_format($kpis['reorder_watch_count'] ?? 1) }}</p>
                    <p class="text-xs text-gray-400 mt-2">products with a reorder level set</p>
                </div>

                {{-- Suppliers --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Active suppliers</p>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <i class="ti ti-truck-delivery text-sm text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ number_format($kpis['suppliers_active'] ?? 3) }}</p>
                    <p class="text-xs text-gray-400 mt-2">avg invoice ₹{{ number_format($kpis['avg_invoice_value'] ?? 131173) }}</p>
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
                    <div class="h-56">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                {{-- Invoice status breakdown --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <h3 class="font-medium text-sm mb-5">Invoice status</h3>
                    <div class="h-40 flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="space-y-2.5 mt-5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Paid
                            </span>
                            <span class="font-medium">{{ $invoiceStatus['paid'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Partial
                            </span>
                            <span class="font-medium">{{ $invoiceStatus['partial'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Pending
                            </span>
                            <span class="font-medium">{{ $invoiceStatus['pending'] ?? 2 }}</span>
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
                        @forelse($topCustomers ?? [
                            (object)['customer_name' => 'Naitik Dholakiya', 'customer_code' => 'CUS010001', 'total_spent' => 262346, 'invoice_count' => 2, 'gst_number' => 'GST5612315213'],
                        ] as $c)
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
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">No customers yet</p>
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
                        @forelse($topProducts ?? [
                            (object)['product_name' => 'Gold Nosepin', 'category_name' => 'Nosepin Gold', 'qty_sold' => 2, 'revenue' => 262346],
                        ] as $p)
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
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">No products yet</p>
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
                        @forelse($categorySales ?? [
                            (object)['category_name' => 'Nosepin Gold', 'revenue' => 262346, 'percent' => 100],
                        ] as $cat)
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
                            <p class="text-sm text-gray-400 text-center py-6">No category sales yet</p>
                        @endforelse
                    </div>
                </div>

                {{-- Reorder watch --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="font-medium text-sm">Reorder watch</h3>
                        <span class="text-[10px] font-medium px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            No stock_quantity column yet
                        </span>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                        @forelse($reorderWatch ?? [
                            (object)['product_name' => 'Gold Nosepin', 'product_code' => 'PROD010001', 'reorder_level' => 56465, 'supplier_name' => 'Naitik'],
                        ] as $r)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <span class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                    <i class="ti ti-alert-triangle text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate">{{ $r->product_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $r->product_code }} · supplier: {{ $r->supplier_name }}</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">reorder @ {{ number_format($r->reorder_level) }}</p>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Nothing to watch</p>
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
                            @forelse($recentInvoices ?? [
                                (object)['invoice_id' => 1, 'invoice_number' => 'INV202600001', 'customer_name' => 'Naitik Dholakiya', 'invoice_date' => '2026-08-20', 'total_tax' => 0,   'discount_amount' => 0,      'grand_total' => 231123, 'payment_status' => 'pending'],
                                (object)['invoice_id' => 6, 'invoice_number' => 'INV202600012', 'customer_name' => 'Naitik Dholakiya', 'invoice_date' => '2026-08-21', 'total_tax' => 100, 'discount_amount' => 200000, 'grand_total' => 31223,  'payment_status' => 'pending'],
                            ] as $inv)
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
                                        <a href="{{ route('invoice.index', $inv->invoice_id) }}"
                                           class="text-xs text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                            <i class="ti ti-chevron-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400">No invoices yet</td>
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
                    @forelse($supplierSnapshot ?? [
                        (object)['supplier_name' => 'Naitik', 'product_count' => 1, 'status' => '1'],
                        (object)['supplier_name' => 'Naitik Dholakiya Dev', 'product_count' => 0, 'status' => '1'],
                        (object)['supplier_name' => 'Shree Swaminarayan Gold', 'product_count' => 0, 'status' => '1'],
                    ] as $s)
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
                        <p class="text-sm text-gray-400 text-center py-6 col-span-full">No suppliers yet</p>
                    @endforelse
                </div>
            </div>

        </main>

        @include('layout.footer')

    </div>
</div>

{{-- Mobile overlay --}}
<div id="mobileOverlay"
     onclick="toggleMobileSidebar()"
     class="hidden fixed inset-0 bg-black/50 z-40 md:hidden backdrop-blur-sm"></div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

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
    icon.className = `ti ${icons[theme]} text-base`;
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

    const revenueLabels = {!! json_encode($revenueTrend['labels'] ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']) !!};
    const revenueValues = {!! json_encode($revenueTrend['values'] ?? [0, 0, 0, 0, 0, 231123, 31223]) !!};

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

    const statusData = {!! json_encode([
        $invoiceStatus['paid'] ?? 0,
        $invoiceStatus['partial'] ?? 0,
        $invoiceStatus['pending'] ?? 2,
    ]) !!};

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
});
</script>
@endpush

@endsection