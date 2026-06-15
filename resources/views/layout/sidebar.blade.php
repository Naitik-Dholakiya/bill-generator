<aside id="sidebar" class="sidebar w-60 bg-white dark:bg-zinc-900 border-r border-gray-100 dark:border-zinc-800
           flex flex-col h-full transition-transform duration-300
           fixed md:relative -translate-x-full md:translate-x-0 z-50 md:z-auto">

    {{-- Logo --}}
    <div class="h-14 border-b border-gray-100 dark:border-zinc-800 px-4 flex items-center gap-3 flex-shrink-0">
        <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-purple-600 rounded-xl
                    flex items-center justify-center text-white font-medium text-sm flex-shrink-0">V</div>
        <span class="font-medium text-[15px] tracking-tight">Vyapar</span>
    </div>

    {{-- User --}}
    <div class="px-4 py-3 border-b border-gray-100 dark:border-zinc-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-cyan-500 to-purple-600 rounded-xl
                        flex items-center justify-center text-white font-medium text-xs flex-shrink-0">
                {{ strtoupper(substr(request()->cookie('CSGO') ?? 'AD', 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="font-medium text-sm truncate">{{ request()->cookie('CSGO') ?? 'Admin' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto p-2" aria-label="Main navigation">

        <p class="px-3 pt-3 pb-1 text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            Main</p>

        <a href="{{ route('dashboard') }}"
            class="nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors mb-0.5
                   {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-medium' : '' }}">
            <i class="ti ti-layout-dashboard text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Dashboard</span>
        </a>
        {{-- {{ route('customers.index') }} --}}
        <a href="#"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors mb-0.5
                   {{ request()->routeIs('customers.*') ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-medium' : '' }}">
            <i class="ti ti-users text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Customers</span>
        </a>
        {{-- {{ route('products.index') }} --}}
        <a href="#"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors mb-0.5
                   {{ request()->routeIs('products.*') ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-medium' : '' }}">
            <i class="ti ti-package text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Products</span>
        </a>

        <p class="px-3 pt-5 pb-1 text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            Finance</p>
        {{-- {{ route('invoices.index') }} --}}
        <a href="#"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors mb-0.5
                   {{ request()->routeIs('invoices.*') ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-medium' : '' }}">
            <i class="ti ti-file-invoice text-base flex-shrink-0" aria-hidden="true"></i>
            <span class="flex-1">Invoices</span>
            @if($pendingInvoices ?? 0)
                <span
                    class="bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 text-[10px] font-medium px-2 py-0.5 rounded-full">
                    {{ $pendingInvoices }}
                </span>
            @endif
        </a>
        {{-- {{ route('payments.index') }} --}}
        <a href="#"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors mb-0.5
                   {{ request()->routeIs('payments.*') ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-medium' : '' }}">
            <i class="ti ti-credit-card text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Payments</span>
        </a>
        {{-- {{ route('reports.index') }} --}}
        <a href="#"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors mb-0.5
                   {{ request()->routeIs('reports.*') ? 'bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white font-medium' : '' }}">
            <i class="ti ti-chart-bar text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Reports</span>
        </a>

    </nav>

    {{-- Footer actions --}}
    <div class="p-2 border-t border-gray-100 dark:border-zinc-800 flex-shrink-0 space-y-0.5">
        {{-- {{ route('settings.index') }} --}}
        <a href="{{ route('settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors
                   {{ request()->routeIs('settings') ? 'bg-gray-100 dark:bg-zinc-800 font-medium' : '' }}">
            <i class="ti ti-settings text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Settings</span>
        </a>

        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); if(confirm('Are you sure you want to logout?')) document.getElementById('logout-form').submit();"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-600 dark:text-red-400
                   hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
            <i class="ti ti-logout text-base flex-shrink-0" aria-hidden="true"></i>
            <span>Logout</span>
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>

</aside>