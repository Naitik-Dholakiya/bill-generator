@php
    $isAdminSidebar = request()->routeIs('admin.*');
    $authenticatedAdmin = request()->attributes->get('authenticated_admin');
    $sidebarName = $isAdminSidebar
        ? ($authenticatedAdmin ? $authenticatedAdmin->full_name : 'Administrator')
        : (request()->cookie('CSGO') ?? 'Admin');
    $sidebarInitials = collect(preg_split('/\s+/', trim($sidebarName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<aside id="sidebar" aria-label="Main sidebar"
    class="fixed inset-y-0 left-0 z-50 flex h-dvh w-72 max-w-[calc(100vw-2rem)] -translate-x-full flex-col overflow-hidden border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition-transform duration-300 ease-in-out dark:border-zinc-800 dark:bg-zinc-900 lg:static lg:z-auto lg:h-full lg:w-64 lg:max-w-none lg:translate-x-0 lg:shadow-none">

    <div class="flex h-14 shrink-0 items-center justify-between border-b border-slate-100 px-4 dark:border-zinc-800">
        <a href="{{ $isAdminSidebar ? route('admin.dashboard') : route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-cyan-500/20" aria-label="{{ $isAdminSidebar ? 'Billora admin console' : 'Vyapar home' }}">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-base font-bold text-white shadow-md shadow-cyan-900/20">
                {{ $isAdminSidebar ? 'B' : 'V' }}
            </span>
            <span class="min-w-0">
                <span class="block truncate text-[15px] font-bold tracking-tight text-slate-900 dark:text-white">{{ $isAdminSidebar ? 'Billora' : 'Vyapar' }}</span>
                <span class="block text-[10px] font-medium uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">{{ $isAdminSidebar ? 'Admin console' : 'Billing workspace' }}</span>
            </span>
        </a>
        <button type="button" onclick="{{ $isAdminSidebar ? 'closeAdminSidebar()' : 'closeMobileSidebar()' }}" aria-label="Close navigation menu"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white lg:hidden">
            <i class="ti ti-x text-xl" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="flex min-h-0 flex-1 flex-col overflow-hidden px-3 py-3" aria-label="Main navigation">
        @if ($isAdminSidebar)
            <p class="px-3 pb-2 text-[9px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">Workspace</p>
            <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                class="mb-1 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                <i class="ti ti-layout-dashboard w-5 shrink-0 text-lg" aria-hidden="true"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('admin.users.index') }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif
                class="mb-1 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                <i class="ti ti-users w-5 shrink-0 text-lg" aria-hidden="true"></i>
                <span>User accounts</span>
            </a>
            <div class="mt-5 rounded-xl border border-cyan-100 bg-cyan-50/70 p-3 dark:border-cyan-900/40 dark:bg-cyan-950/20">
                <p class="text-xs font-semibold text-cyan-900 dark:text-cyan-200">Account controls</p>
                <p class="mt-1 text-xs leading-5 text-cyan-800/75 dark:text-cyan-300/75">Manage roles, access status, and workspace assignments.</p>
            </div>
        @else

        <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif
            class="mb-1 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors
                {{ request()->routeIs('dashboard') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-layout-dashboard w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Dashboard</span>
            @if (request()->routeIs('dashboard'))
                <span class="ml-auto h-1.5 w-1.5 rounded-full bg-cyan-600 dark:bg-cyan-400"></span>
            @endif
        </a>

        <p class="px-3 pb-1 pt-3 text-[9px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">
            Manage
        </p>

        <a href="{{ route('customers.index') }}" @if (request()->routeIs('customers.*')) aria-current="page" @endif
            class="mb-0.5 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors
                {{ request()->routeIs('customers.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-users w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Customers</span>
        </a>

        <a href="{{ route('suppliers.index') }}" @if (request()->routeIs('suppliers.*')) aria-current="page" @endif
            class="mb-0.5 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors
                {{ request()->routeIs('suppliers.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-truck-delivery w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Suppliers</span>
        </a>

        <a href="{{ route('products.index') }}" @if (request()->routeIs('products.*')) aria-current="page" @endif
            class="mb-0.5 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors
                {{ request()->routeIs('products.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-package w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span>Products</span>
        </a>

        <a href="{{ route('invoice.index') }}" @if (request()->routeIs('invoice.*')) aria-current="page" @endif
            class="mb-0.5 flex min-h-9 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors
                {{ request()->routeIs('invoice.*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
            <i class="ti ti-file-invoice w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span class="flex-1">Invoices</span>
            @if ($pendingInvoices ?? 0)
                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                    {{ $pendingInvoices }}
                </span>
            @endif
        </a>

        <p class="px-3 pb-1 pt-3 text-[9px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-zinc-500">
            Coming soon
        </p>
        <div aria-disabled="true" class="mb-0.5 flex min-h-9 cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-400 dark:text-zinc-500">
            <i class="ti ti-box-seam w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span class="flex-1">Stock</span>
            <span class="rounded-full border border-slate-200 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-400 dark:border-zinc-700 dark:text-zinc-500">Planned</span>
        </div>
        <div aria-disabled="true" class="mb-0.5 flex min-h-9 cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-400 dark:text-zinc-500">
            <i class="ti ti-receipt-2 w-5 shrink-0 text-lg" aria-hidden="true"></i>
            <span class="flex-1">Supplier bills</span>
            <span class="rounded-full border border-slate-200 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-400 dark:border-zinc-700 dark:text-zinc-500">Planned</span>
        </div>
        @endif
    </nav>

    @if ($isAdminSidebar)
        <div class="shrink-0 border-t border-slate-100 px-4 py-3 text-xs text-slate-400 dark:border-zinc-800 dark:text-zinc-500">
            <i class="ti ti-shield-lock mr-1 text-emerald-500" aria-hidden="true"></i> Administrator session
        </div>
    @else
    <div class="shrink-0 border-t border-slate-100 bg-white px-3 pb-3 pt-2 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-2 flex min-w-0 items-center gap-2 px-1">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-indigo-600 text-[10px] font-bold text-white">
                {{ $sidebarInitials ?: 'U' }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-xs font-semibold text-slate-700 dark:text-zinc-200">{{ $sidebarName }}</span>
                <span class="block truncate text-[10px] text-slate-400 dark:text-zinc-500">Workspace account</span>
            </span>
            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500" title="Online"></span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings') }}" @if (request()->routeIs('settings*')) aria-current="page" @endif
                class="flex min-h-9 flex-1 items-center justify-center gap-2 rounded-lg px-2 text-xs font-medium transition-colors
                    {{ request()->routeIs('settings*') ? 'bg-cyan-50 font-semibold text-cyan-800 dark:bg-cyan-950/50 dark:text-cyan-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
                <i class="ti ti-settings text-base" aria-hidden="true"></i>
                <span>Settings</span>
            </a>
            <button type="button" onclick="openLogoutModal(event)"
                class="flex min-h-9 flex-1 items-center justify-center gap-2 rounded-lg px-2 text-xs font-medium text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-300 dark:hover:bg-red-950/30 dark:hover:text-red-400">
                <i class="ti ti-logout text-base" aria-hidden="true"></i>
                <span>Log out</span>
            </button>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
    @endif
</aside>

<div id="mobileOverlay" aria-hidden="true" onclick="{{ $isAdminSidebar ? 'closeAdminSidebar()' : 'closeMobileSidebar()' }}"
    class="invisible fixed inset-0 z-40 bg-slate-950/50 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden"></div>
