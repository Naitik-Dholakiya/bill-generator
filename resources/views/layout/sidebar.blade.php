@php
    $sidebarName = request()->cookie('CSGO') ?? 'Admin';
    $sidebarIsAdmin = \Illuminate\Support\Facades\DB::table('usermaster')
        ->where('user_id', request()->cookie('GTA'))
        ->where('is_admin', 'Y')
        ->exists();
    $sidebarInitials = collect(preg_split('/\s+/', trim($sidebarName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<aside id="sidebar" aria-label="Main sidebar"
    class="fixed inset-y-0 left-0 z-50 flex h-dvh w-72 max-w-[calc(100vw-2rem)] -translate-x-full flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition-transform duration-300 ease-in-out dark:border-zinc-800 dark:bg-zinc-900 lg:static lg:z-auto lg:h-full lg:w-60 lg:max-w-none lg:translate-x-0 lg:shadow-none">

    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-100 px-5 dark:border-zinc-800">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-cyan-500/20" aria-label="Vyapar home">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-lg font-bold text-white shadow-lg shadow-cyan-900/15">
                V
            </span>
            <span class="min-w-0">
                <span class="block truncate text-[15px] font-bold tracking-tight text-slate-900 dark:text-white">Vyapar</span>
                <span class="block text-[10px] font-medium uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">Billing workspace</span>
            </span>
        </a>
        <button type="button" onclick="closeMobileSidebar()" aria-label="Close navigation menu"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white lg:hidden">
            <i class="ti ti-x text-xl" aria-hidden="true"></i>
        </button>
    </div>

    <div class="shrink-0 border-b border-slate-100 p-4 dark:border-zinc-800">
        <div class="flex min-w-0 items-center gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-zinc-800/70">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-xs font-bold text-white">
                {{ $sidebarInitials ?: 'U' }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold text-slate-800 dark:text-zinc-100">{{ $sidebarName }}</span>
                <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-zinc-400">Workspace account</span>
            </span>
            <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500 ring-4 ring-emerald-500/10" title="Online"></span>
        </div>
    </div>

    <nav class="sidebar-navigation app-scrollbar min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-4" aria-label="Main navigation">
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">
            Workspace
        </p>

        <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif
            class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                {{ request()->routeIs('dashboard') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-layout-dashboard w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Dashboard</span>
            @if (request()->routeIs('dashboard'))
                <span class="ml-auto h-1.5 w-1.5 rounded-full bg-cyan-600 dark:bg-cyan-400"></span>
            @endif
        </a>

        <p class="px-3 pb-2 pt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">
            Manage
        </p>

        <a href="{{ route('customers.index') }}" @if (request()->routeIs('customers.*')) aria-current="page" @endif
            class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                {{ request()->routeIs('customers.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-users w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Customers</span>
        </a>

        <a href="{{ route('suppliers.index') }}" @if (request()->routeIs('suppliers.*')) aria-current="page" @endif
            class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                {{ request()->routeIs('suppliers.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-truck-delivery w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Suppliers</span>
        </a>

        <a href="{{ route('products.index') }}" @if (request()->routeIs('products.*')) aria-current="page" @endif
            class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                {{ request()->routeIs('products.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-package w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Products</span>
        </a>

        <a href="{{ route('invoice.index') }}" @if (request()->routeIs('invoice.*')) aria-current="page" @endif
            class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                {{ request()->routeIs('invoice.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-file-invoice w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span class="flex-1">Invoices</span>
            @if ($pendingInvoices ?? 0)
                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                    {{ $pendingInvoices }}
                </span>
            @endif
        </a>

        {{-- <p class="px-3 pb-2 pt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">
            Finance
        </p>

        <a href="#" class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">
            <i class="ti ti-credit-card w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Payments</span>
        </a>

        <a href="#" class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">
            <i class="ti ti-chart-bar w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Reports</span>
        </a> --}}
    </nav>

    <div class="shrink-0 border-t border-slate-100 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
        <a href="{{ route('settings') }}" @if (request()->routeIs('settings*')) aria-current="page" @endif
            class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                {{ request()->routeIs('settings*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-settings w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Settings</span>
        </a>
        @if ($sidebarIsAdmin)
            <a href="{{ route('admin.jewelry-users.index') }}" @if (request()->routeIs('admin.jewelry-users.*')) aria-current="page" @endif
                class="mb-1 flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors
                    {{ request()->routeIs('admin.jewelry-users.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                <i class="ti ti-users-group w-5 shrink-0 text-lg" aria-hidden="true"></i>
                <span>Jewelry accounts</span>
            </a>
        @endif

        <button type="button" onclick="openLogoutModal(event)"
            class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-300 dark:hover:bg-red-950/30 dark:hover:text-red-400">
            <i class="ti ti-logout w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Log out</span>
        </button>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        <div class="mt-3 flex items-center justify-center gap-2 border-t border-slate-100 pt-3 text-[10px] font-medium tracking-wide text-slate-400 dark:border-zinc-800 dark:text-zinc-500">
            <i class="ti ti-shield-check text-sm text-emerald-500" aria-hidden="true"></i>
            <span>SECURE WORKSPACE</span>
        </div>
    </div>
</aside>

<div id="mobileOverlay" aria-hidden="true" onclick="closeMobileSidebar()"
    class="invisible fixed inset-0 z-40 bg-slate-950/50 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden"></div>
