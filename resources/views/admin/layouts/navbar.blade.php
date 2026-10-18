<header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 dark:border-zinc-800 dark:bg-zinc-900 sm:px-7">
    <div class="flex min-w-0 items-center gap-3">
        <button id="adminMobileMenuButton" type="button" onclick="toggleAdminSidebar()" aria-label="Open admin navigation" aria-controls="sidebar" aria-expanded="false"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-violet-500/20 dark:text-zinc-300 dark:hover:bg-zinc-800 lg:hidden">
            <i class="ti ti-menu-2 text-xl" aria-hidden="true"></i>
        </button>
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-cyan-700 dark:text-cyan-400">Administration</p>
            <h1 class="truncate text-sm font-semibold text-slate-900 dark:text-white">@yield('heading', 'Control center')</h1>
        </div>
    </div>
    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        <span class="hidden text-sm text-slate-500 dark:text-zinc-400 sm:block">{{ request()->attributes->get('authenticated_admin')->full_name }}</span>
        <button id="adminThemeToggle" type="button" onclick="toggleAdminTheme()" aria-label="Switch color theme"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-violet-500/20 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
            title="Switch theme">
            <i id="adminThemeIcon" class="ti ti-sun text-lg" aria-hidden="true"></i>
        </button>
        <form id="adminLogoutForm" action="{{ route('logout') }}" method="POST" onsubmit="confirmAdminLogout(event)">
            @csrf
            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-red-900 dark:hover:bg-red-950/40 dark:hover:text-red-300">
                <i class="ti ti-logout" aria-hidden="true"></i><span class="hidden sm:inline">Sign out</span>
            </button>
        </form>
    </div>
</header>
