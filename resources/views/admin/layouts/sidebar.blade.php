<aside id="adminSidebar" aria-label="Administrator navigation"
    class="fixed inset-y-0 left-0 z-50 flex h-dvh w-72 max-w-[calc(100vw-2rem)] -translate-x-full flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition-transform duration-300 ease-in-out dark:border-zinc-800 dark:bg-zinc-900 md:static md:z-auto md:h-full md:w-64 md:max-w-none md:translate-x-0 md:shadow-none">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-100 px-5 dark:border-zinc-800">
        <a href="{{ route('admin.dashboard') }}" aria-label="Billora admin console" class="flex min-w-0 items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 text-lg font-bold text-white shadow-lg shadow-indigo-900/20">B</span>
        <span>
            <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-white">Billora</span>
            <span class="block text-[10px] font-semibold uppercase tracking-[0.15em] text-violet-600 dark:text-violet-400">Admin console</span>
        </span>
        </a>
        <button type="button" onclick="closeAdminSidebar()" aria-label="Close admin navigation"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:text-zinc-400 dark:hover:bg-zinc-800 md:hidden">
            <i class="ti ti-x text-lg" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="app-scrollbar min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-5" aria-label="Administrator workspace links">
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500">Workspace</p>
        <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
            class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-violet-50 text-violet-800 dark:bg-violet-950/50 dark:text-violet-300' : 'text-slate-600 hover:bg-slate-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
            <i class="ti ti-layout-dashboard text-lg" aria-hidden="true"></i><span>Overview</span>
        </a>
        <a href="{{ route('admin.users.index') }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif
            class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-violet-50 text-violet-800 dark:bg-violet-950/50 dark:text-violet-300' : 'text-slate-600 hover:bg-slate-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
            <i class="ti ti-users text-lg" aria-hidden="true"></i><span>User accounts</span>
        </a>
        <div class="mt-6 rounded-2xl border border-violet-100 bg-violet-50/70 p-4 dark:border-violet-900/40 dark:bg-violet-950/20">
            <p class="text-xs font-semibold text-violet-900 dark:text-violet-200">Account controls</p>
            <p class="mt-1 text-xs leading-5 text-violet-800/75 dark:text-violet-300/75">Manage roles, access status, jewelry Touch, and invoice template assignments.</p>
        </div>
    </nav>
    <div class="shrink-0 border-t border-slate-100 px-5 py-4 text-xs text-slate-400 dark:border-zinc-800 dark:text-zinc-500">
        <i class="ti ti-shield-lock mr-1 text-emerald-500" aria-hidden="true"></i> Administrator session
    </div>
</aside>
