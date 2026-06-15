<header
    class="h-14 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800
           px-5 flex items-center gap-3 shrink-0">

    {{-- Mobile Menu --}}
    <button
        onclick="toggleMobileSidebar()"
        class="md:hidden w-9 h-9 flex items-center justify-center rounded-xl
               hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
        <i class="ti ti-menu-2 text-lg"></i>
    </button>

    {{-- Page Title --}}
    <h1 id="pageTitle" class="font-semibold text-sm flex-1">
        {{ $title ?? 'Dashboard' }}
    </h1>

    {{-- Search --}}
    <div
        class="hidden lg:flex items-center gap-2 w-72 h-9 px-3 rounded-xl
               bg-gray-100 dark:bg-zinc-800">

        <i class="ti ti-search text-gray-400"></i>

        <input
            type="text"
            placeholder="Search..."
            class="flex-1 bg-transparent outline-none text-sm">
    </div>

    {{-- Notifications --}}
    <button
        class="relative w-9 h-9 rounded-xl flex items-center justify-center
               hover:bg-gray-100 dark:hover:bg-zinc-800 transition">

        <i class="ti ti-bell"></i>

        <span
            class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500">
        </span>
    </button>

    {{-- Theme --}}
    <div class="relative" id="themeWrapper">

        <button
            id="themeToggleBtn"
            onclick="toggleThemeMenu()"
            class="w-9 h-9 rounded-xl flex items-center justify-center
                   hover:bg-gray-100 dark:hover:bg-zinc-800 transition">

            <i id="themeIcon" class="ti ti-sun"></i>
        </button>

        <div
            id="themeMenu"
            class="hidden absolute right-0 mt-2 w-44 bg-white dark:bg-zinc-900
                   rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700
                   overflow-hidden z-50">

            <button onclick="setTheme('light')"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm
                       hover:bg-gray-100 dark:hover:bg-zinc-800">
                <i class="ti ti-sun"></i>
                Light
            </button>

            <button onclick="setTheme('dark')"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm
                       hover:bg-gray-100 dark:hover:bg-zinc-800">
                <i class="ti ti-moon"></i>
                Dark
            </button>

            <button onclick="setTheme('system')"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm
                       hover:bg-gray-100 dark:hover:bg-zinc-800">
                <i class="ti ti-device-laptop"></i>
                System
            </button>

        </div>

    </div>

    {{-- User --}}
    <button
        class="h-9 px-2 rounded-xl flex items-center gap-2
               hover:bg-gray-100 dark:hover:bg-zinc-800 transition">

        <div
            class="w-8 h-8 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600
                   text-white flex items-center justify-center text-xs font-semibold">

            {{ strtoupper(substr(request()->cookie('CSGO') ?? 'AD', 0, 2)) }}
        </div>
    </button>

</header>