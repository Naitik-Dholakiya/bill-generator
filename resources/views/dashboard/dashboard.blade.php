@extends('layouts.app')

@section('content')
<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        @include('layout.navbar')

        <main class="flex-1 overflow-y-auto p-5 space-y-5">

            {{-- Welcome --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-6
                        flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-medium">
                        Welcome back,
                        <span class="bg-gradient-to-r from-cyan-500 to-purple-600 bg-clip-text text-transparent">
                            {{ request()->cookie('CSGO') ?? 'Admin' }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Here's what's happening with your business today.</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 dark:border-zinc-700
                                   rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <i class="ti ti-download text-base" aria-hidden="true"></i> Export
                    </button>
                    {{-- {{ route('invoices.create') }} --}}
                    <a href="#"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl
                              bg-gradient-to-r from-cyan-500 to-purple-600 hover:opacity-90 transition-opacity">
                        <i class="ti ti-plus text-base" aria-hidden="true"></i> New invoice
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</p>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <i class="ti ti-currency-rupee text-sm text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">₹{{ number_format($stats['revenue'] ?? 124500) }}</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 flex items-center gap-1">
                        <i class="ti ti-trending-up text-sm" aria-hidden="true"></i>
                        12.4% <span class="text-gray-400">this month</span>
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Customers</p>
                        <span class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center">
                            <i class="ti ti-users text-sm text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ number_format($stats['customers'] ?? 5462) }}</p>
                    <p class="text-xs text-cyan-600 dark:text-cyan-400 mt-2 flex items-center gap-1">
                        <i class="ti ti-trending-up text-sm" aria-hidden="true"></i>
                        8.1% <span class="text-gray-400">this month</span>
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Orders</p>
                        <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                            <i class="ti ti-shopping-bag text-sm text-purple-600 dark:text-purple-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ number_format($stats['orders'] ?? 2185) }}</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 flex items-center gap-1">
                        <i class="ti ti-trending-up text-sm" aria-hidden="true"></i>
                        5.4% <span class="text-gray-400">this month</span>
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Growth</p>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="ti ti-trending-up text-sm text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-medium">{{ $stats['growth'] ?? 24 }}%</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 flex items-center gap-1">
                        <i class="ti ti-trending-up text-sm" aria-hidden="true"></i>
                        3.2% <span class="text-gray-400">this month</span>
                    </p>
                </div>

            </div>

            {{-- Charts + Activity --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Revenue Chart --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-medium text-sm">Revenue analytics</h3>
                        <select class="text-xs bg-gray-100 dark:bg-zinc-800 border-0 rounded-lg px-3 py-1.5
                                       text-gray-600 dark:text-gray-300 outline-none cursor-pointer" aria-label="Period">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2 h-40">
                        @foreach([35, 55, 75, 45, 90, 60, 80] as $i => $height)
                            @php $labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']; @endphp
                            <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                                <div class="w-full bg-gradient-to-t from-cyan-500 to-cyan-400 dark:from-cyan-600 dark:to-cyan-500
                                            rounded-t-lg hover:opacity-75 transition-opacity cursor-pointer"
                                     style="height: {{ $height }}%"
                                     title="{{ $labels[$i] }}: {{ $height }}%"></div>
                                <span class="text-[10px] text-gray-400">{{ $labels[$i] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-medium text-sm">Recent activity</h3>
                        <button class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline">View all</button>
                    </div>
                    <div class="space-y-0">
                        @foreach($recentActivity ?? [
                            ['dot' => 'bg-cyan-500',   'title' => 'New customer registered',       'time' => '5 minutes ago',  'badge' => 'New',     'color' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300'],
                            ['dot' => 'bg-purple-500', 'title' => 'Invoice #1042 generated',       'time' => '20 minutes ago', 'badge' => 'Invoice', 'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'],
                            ['dot' => 'bg-emerald-500','title' => 'Payment of ₹12,000 received',   'time' => '1 hour ago',     'badge' => 'Paid',    'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
                            ['dot' => 'bg-amber-500',  'title' => 'Invoice #1038 overdue',         'time' => '3 hours ago',    'badge' => 'Overdue', 'color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                            ['dot' => 'bg-cyan-500',   'title' => 'Product "Pro Plan" updated',    'time' => '5 hours ago',    'badge' => 'Update',  'color' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300'],
                        ] as $item)
                            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 dark:border-zinc-800 last:border-0">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $item['dot'] }}"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate">{{ $item['title'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item['time'] }}</p>
                                </div>
                                <span class="text-[10px] font-medium px-2.5 py-0.5 rounded-full flex-shrink-0 {{ $item['color'] }}">
                                    {{ $item['badge'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Recent Orders --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                    <h3 class="font-medium text-sm">Recent orders</h3>
                    <div class="flex items-center gap-2">
                        <select class="text-xs bg-gray-100 dark:bg-zinc-800 border-0 rounded-lg px-3 py-1.5
                                       text-gray-600 dark:text-gray-300 outline-none cursor-pointer" aria-label="Filter status">
                            <option value="">All status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                        </select>
                        {{-- {{ route('orders.index') }} --}}
                        <a href="#"
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
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Order ID</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Customer</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Product</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Amount</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Date</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-zinc-800/50">Status</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-zinc-800/50"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-zinc-800">
                            {{-- @forelse($recentOrders ?? [] as $order)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-cyan-600 dark:text-cyan-400">#{{ $order->id }}</td>
                                    <td class="px-6 py-3.5">{{ $order->customer->name }}</td>
                                    <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400">{{ $order->product->name }}</td>
                                    <td class="px-6 py-3.5 font-medium">₹{{ number_format($order->amount) }}</td>
                                    <td class="px-6 py-3.5 text-gray-400">{{ $order->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($order->status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                        @elseif($order->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pending</span>
                                        @elseif($order->status === 'overdue')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Overdue</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('orders.show', $order) }}"
                                           class="text-xs text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                            <i class="ti ti-chevron-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                {{-- Fallback demo rows --}}
                                @foreach([
                                    ['id'=>'1001','customer'=>'John Doe',     'product'=>'Pro Plan',    'amount'=>'5,000',  'date'=>'14 Jun 2026','status'=>'paid'],
                                    ['id'=>'1002','customer'=>'Priya Sharma', 'product'=>'Basic Plan',  'amount'=>'2,500',  'date'=>'13 Jun 2026','status'=>'pending'],
                                    ['id'=>'1003','customer'=>'Rahul Verma',  'product'=>'Enterprise',  'amount'=>'18,000', 'date'=>'13 Jun 2026','status'=>'paid'],
                                    ['id'=>'1004','customer'=>'Anita Mehta',  'product'=>'Pro Plan',    'amount'=>'5,000',  'date'=>'12 Jun 2026','status'=>'overdue'],
                                    ['id'=>'1005','customer'=>'Kiran Patel',  'product'=>'Basic Plan',  'amount'=>'2,500',  'date'=>'11 Jun 2026','status'=>'paid'],
                                ] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-cyan-600 dark:text-cyan-400">#{{ $row['id'] }}</td>
                                    <td class="px-6 py-3.5">{{ $row['customer'] }}</td>
                                    <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400">{{ $row['product'] }}</td>
                                    <td class="px-6 py-3.5 font-medium">₹{{ $row['amount'] }}</td>
                                    <td class="px-6 py-3.5 text-gray-400">{{ $row['date'] }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($row['status'] === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                        @elseif($row['status'] === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pending</span>
                                        @elseif($row['status'] === 'overdue')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Overdue</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <i class="ti ti-chevron-right text-gray-300 dark:text-gray-600" aria-hidden="true"></i>
                                    </td>
                                </tr>
                                @endforeach
                            {{-- @endforelse --}}
                        </tbody>
                    </table>
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
</script>
@endpush

@endsection