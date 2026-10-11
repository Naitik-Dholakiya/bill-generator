@php
    $navName = request()->cookie('CSGO') ?? 'Admin';
    $navInitials = collect(preg_split('/\s+/', trim($navName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<header class="relative z-30 flex min-h-16 shrink-0 items-center gap-2 border-b border-slate-200 bg-white/95 px-3 backdrop-blur sm:gap-3 sm:px-5 dark:border-zinc-800 dark:bg-zinc-900/95">
    <button id="mobileMenuButton" type="button" onclick="toggleMobileSidebar()" aria-label="Open navigation menu"
        aria-controls="sidebar" aria-expanded="false"
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 dark:text-zinc-300 dark:hover:bg-zinc-800 lg:hidden">
        <i class="ti ti-menu-2 text-xl" aria-hidden="true"></i>
    </button>

    <h1 id="pageTitle" class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-900 sm:text-base dark:text-white">
        {{ $title ?? 'Dashboard' }}
    </h1>

    <div class="hidden h-10 w-56 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-slate-400 transition focus-within:border-cyan-500 focus-within:ring-4 focus-within:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 xl:flex 2xl:w-72">
        <i class="ti ti-search text-base" aria-hidden="true"></i>
        <input type="search" aria-label="Search" placeholder="Search workspace"
            class="min-w-0 flex-1 bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400 dark:text-zinc-100 dark:placeholder:text-zinc-500">
        <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] text-slate-400 dark:border-zinc-700 dark:bg-zinc-800">/</kbd>
    </div>

    <div class="relative shrink-0" id="themeWrapper">
        <button id="themeToggleBtn" type="button" onclick="toggleThemeMenu()" aria-label="Choose color theme"
            aria-controls="themeMenu" aria-expanded="false"
            class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 dark:text-zinc-300 dark:hover:bg-zinc-800">
            <i id="themeIcon" class="ti ti-sun text-lg" aria-hidden="true"></i>
        </button>

        <div id="themeMenu" role="menu" aria-labelledby="themeToggleBtn"
            class="absolute right-0 top-full z-50 mt-2 hidden w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="px-3 pb-1 pt-2 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-zinc-500">Appearance</p>
            <button type="button" role="menuitem" onclick="setTheme('light')"
                class="flex min-h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <i class="ti ti-sun text-base" aria-hidden="true"></i> Light
            </button>
            <button type="button" role="menuitem" onclick="setTheme('dark')"
                class="flex min-h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <i class="ti ti-moon text-base" aria-hidden="true"></i> Dark
            </button>
            <button type="button" role="menuitem" onclick="setTheme('system')"
                class="flex min-h-10 w-full items-center gap-3 rounded-xl px-3 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <i class="ti ti-device-laptop text-base" aria-hidden="true"></i> System
            </button>
        </div>
    </div>

    <a href="{{ route('settings') }}" aria-label="Account settings for {{ $navName }}"
        class="group flex h-10 shrink-0 items-center gap-2 rounded-xl p-1 pr-1.5 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 dark:hover:bg-zinc-800 sm:pr-2">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-indigo-600 text-[11px] font-bold text-white">
            {{ $navInitials ?: 'U' }}
        </span>
        <span class="hidden max-w-32 truncate text-sm font-medium text-slate-700 group-hover:text-slate-900 sm:block dark:text-zinc-200 dark:group-hover:text-white">
            {{ $navName }}
        </span>
        <i class="ti ti-chevron-down hidden text-xs text-slate-400 sm:block" aria-hidden="true"></i>
    </a>
</header>
