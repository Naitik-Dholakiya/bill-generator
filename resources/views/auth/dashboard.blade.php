@extends('app')

@section('content')
    <div class="min-h-screen bg-gray-100 dark:bg-black transition-colors duration-300 flex">

        <!-- Sidebar -->
        <aside id="sidebar"
            class="w-72 h-screen sticky top-0 bg-white dark:bg-zinc-950 border-r border-gray-200 dark:border-zinc-800 flex flex-col">

            <!-- Logo -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-gray-200 dark:border-zinc-800">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Billing System
                </h1>

                <button id="sidebarToggle"
                    class="lg:hidden w-10 h-10 rounded-xl bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-white">
                    ☰
                </button>
            </div>

            <!-- User -->
            <div class="p-6 border-b border-gray-200 dark:border-zinc-800">

                <div class="flex items-center gap-3">

                    <div
                        class="w-12 h-12 rounded-full bg-gradient-to-r from-cyan-500 to-purple-600 flex items-center justify-center text-white font-bold">

                        {{ strtoupper(substr(request()->cookie('CSGO'), 0, 1)) }}

                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            {{ request()->cookie('CSGO') }}
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Administrator
                        </p>
                    </div>

                </div>

            </div>

            <!-- Menu -->
            <nav class="flex-1 p-4 space-y-2">

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-500/20">

                    🏠 Dashboard

                </a>

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-900">

                    👥 Customers

                </a>

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-900">

                    📦 Products

                </a>

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-900">

                    🧾 Invoices

                </a>

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-900">

                    💳 Payments

                </a>

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-900">

                    📊 Reports

                </a>

                <a href="#"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-900">

                    ⚙️ Settings

                </a>

            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-gray-200 dark:border-zinc-800">

                <a href="/logout"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-medium">

                    Logout

                </a>

            </div>

        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Navbar -->
            <header
                class="h-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-xl border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between px-6">

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Dashboard
                    </h2>
                </div>

                <div class="flex items-center gap-4">

                    <!-- Search -->
                    <div class="hidden md:block">
                        <input type="text" placeholder="Search..."
                            class="w-64 px-4 py-2 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-900 dark:text-white outline-none">
                    </div>

                    <!-- Theme -->
                    <div class="relative">
                        <button id="themeToggle"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">

                            <svg id="themeIcon" class="w-5 h-5 text-yellow-500 transition-all duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                            </svg>

                            <span id="themeText" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                System
                            </span>
                        </button>

                        <div id="themeMenu"
                            class="hidden absolute right-0 mt-2 w-44 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50">

                            <button data-theme="light"
                                class="theme-option w-full px-4 py-3 flex items-center gap-3 hover:bg-gray-100 dark:hover:bg-gray-800">
                                ☀️ Light
                            </button>

                            <button data-theme="dark"
                                class="theme-option w-full px-4 py-3 flex items-center gap-3 hover:bg-gray-100 dark:hover:bg-gray-800">
                                🌙 Dark
                            </button>

                            <button data-theme="system"
                                class="theme-option w-full px-4 py-3 flex items-center gap-3 hover:bg-gray-100 dark:hover:bg-gray-800">
                                💻 System
                            </button>

                        </div>
                    </div>

                    <!-- Notification -->
                    <button class="w-11 h-11 rounded-xl bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-white">

                        🔔

                    </button>

                </div>

            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- Welcome -->
                <div
                    class="rounded-3xl bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 p-8 shadow-sm">

                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white">

                        Welcome Back,

                        <span class="bg-gradient-to-r from-cyan-500 to-purple-600 bg-clip-text text-transparent">

                            {{ request()->cookie('CSGO') }}

                        </span>

                    </h2>

                    <p class="mt-3 text-gray-600 dark:text-gray-400">
                        Here's what's happening in your business today.
                    </p>

                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mt-6">

                    <div class="bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6">

                        <p class="text-gray-500 dark:text-gray-400">
                            Revenue
                        </p>

                        <h3 class="text-3xl font-bold mt-2 text-gray-900 dark:text-white">
                            ₹1,24,500
                        </h3>

                        <span class="text-green-500">
                            +12.4%
                        </span>

                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6">

                        <p class="text-gray-500 dark:text-gray-400">
                            Customers
                        </p>

                        <h3 class="text-3xl font-bold mt-2 text-gray-900 dark:text-white">
                            5,462
                        </h3>

                        <span class="text-cyan-500">
                            +8.1%
                        </span>

                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6">

                        <p class="text-gray-500 dark:text-gray-400">
                            Orders
                        </p>

                        <h3 class="text-3xl font-bold mt-2 text-gray-900 dark:text-white">
                            2,185
                        </h3>

                        <span class="text-purple-500">
                            +5.4%
                        </span>

                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6">

                        <p class="text-gray-500 dark:text-gray-400">
                            Growth
                        </p>

                        <h3 class="text-3xl font-bold mt-2 text-gray-900 dark:text-white">
                            24%
                        </h3>

                        <span class="text-orange-500">
                            This Month
                        </span>

                    </div>

                </div>

                <!-- Analytics -->
                <div class="grid lg:grid-cols-3 gap-6 mt-6">

                    <div
                        class="lg:col-span-2 bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6">

                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                            Revenue Analytics
                        </h3>

                        <div class="h-80 flex items-end gap-4">

                            <div class="w-full bg-cyan-500 rounded-t-xl h-24"></div>
                            <div class="w-full bg-cyan-500 rounded-t-xl h-40"></div>
                            <div class="w-full bg-cyan-500 rounded-t-xl h-56"></div>
                            <div class="w-full bg-cyan-500 rounded-t-xl h-32"></div>
                            <div class="w-full bg-cyan-500 rounded-t-xl h-72"></div>
                            <div class="w-full bg-cyan-500 rounded-t-xl h-48"></div>
                            <div class="w-full bg-cyan-500 rounded-t-xl h-64"></div>

                        </div>

                    </div>

                    <div class="bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl p-6">

                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                            Recent Activity
                        </h3>

                        <div class="space-y-5">

                            <div>
                                <p class="text-gray-900 dark:text-white">
                                    New customer registered
                                </p>
                                <span class="text-sm text-gray-500">
                                    5 min ago
                                </span>
                            </div>

                            <div>
                                <p class="text-gray-900 dark:text-white">
                                    Invoice generated
                                </p>
                                <span class="text-sm text-gray-500">
                                    20 min ago
                                </span>
                            </div>

                            <div>
                                <p class="text-gray-900 dark:text-white">
                                    Payment received
                                </p>
                                <span class="text-sm text-gray-500">
                                    1 hour ago
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Table -->
                <div
                    class="mt-6 bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-3xl overflow-hidden">

                    <div class="p-6 border-b border-gray-200 dark:border-zinc-800">

                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Recent Orders
                        </h3>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr class="border-b border-gray-200 dark:border-zinc-800">

                                    <th class="p-4 text-left text-gray-500">Order ID</th>
                                    <th class="p-4 text-left text-gray-500">Customer</th>
                                    <th class="p-4 text-left text-gray-500">Amount</th>
                                    <th class="p-4 text-left text-gray-500">Status</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr class="border-b border-gray-200 dark:border-zinc-800">

                                    <td class="p-4 text-gray-900 dark:text-white">#1001</td>
                                    <td class="p-4 text-gray-900 dark:text-white">John Doe</td>
                                    <td class="p-4 text-gray-900 dark:text-white">₹5,000</td>

                                    <td class="p-4">
                                        <span
                                            class="px-3 py-1 rounded-full bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 text-xs">
                                            Completed
                                        </span>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </main>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const toggleBtn = document.getElementById('themeToggle');
            const menu = document.getElementById('themeMenu');
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');

            function updateThemeUI(theme) {

                if (theme === 'light') {
                    text.textContent = 'Light';

                    icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                <circle cx="12" cy="12" r="4" stroke-width="2"/>
                `;
                }
                else if (theme === 'dark') {
                    text.textContent = 'Dark';

                    icon.innerHTML = `
                <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646a9 9 0 1011.708 11.708z"/>
                `;
                }
                else {
                    text.textContent = 'System';

                    icon.innerHTML = `
                <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9.75 17L15 12l-5.25-5"/>
                `;
                }
            }

            function applyTheme(theme) {

                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                }
                else if (theme === 'light') {
                    document.documentElement.classList.remove('dark');
                }
                else {

                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }

                localStorage.setItem('theme', theme);
                updateThemeUI(theme);
            }

            const savedTheme = localStorage.getItem('theme') || 'system';
            applyTheme(savedTheme);

            toggleBtn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });

            document.querySelectorAll('.theme-option').forEach(btn => {

                btn.addEventListener('click', () => {

                    const theme = btn.dataset.theme;

                    applyTheme(theme);

                    menu.classList.add('hidden');
                });
            });

            document.addEventListener('click', (e) => {

                if (!toggleBtn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });

        });
    </script>
@endsection