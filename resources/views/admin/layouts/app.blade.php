<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') · Vyapar</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme') || 'system';
            const useDarkTheme = savedTheme === 'dark'
                || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', useDarkTheme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html { scrollbar-width: thin; scrollbar-color: #a8b4c4 transparent; }
        html.dark { scrollbar-color: #52525b transparent; }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb { border: 2px solid transparent; border-radius: 9999px; background: #a8b4c4; background-clip: padding-box; }
        .dark *::-webkit-scrollbar-thumb { background-color: #52525b; }
        *, *::before, *::after {
            transition: background-color 250ms ease, border-color 250ms ease, color 250ms ease, fill 250ms ease, stroke 250ms ease, box-shadow 250ms ease;
        }
        .swal2-timer-progress-bar { background: #accf9c !important; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; scroll-behavior: auto !important; }
        }
    </style>
</head>
<body class="h-screen overflow-hidden bg-slate-50 font-sans text-slate-900 dark:bg-zinc-950 dark:text-zinc-100">
    <div class="flex h-full">
        @include('layout.sidebar')
        <div class="flex min-w-0 flex-1 flex-col">
            @include('admin.layouts.navbar')
            <main class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-7 lg:p-9">
                <div class="mx-auto max-w-7xl">
                    @yield('content')
                </div>
            </main>
            @include('layout.footer')
        </div>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    <div id="logoutModal" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle"
        aria-describedby="logoutModalDescription"
        class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        onclick="if (event.target === this) closeLogoutModal()">
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="relative px-6 pb-5 pt-7 text-center sm:px-8">
                <button type="button" onclick="closeLogoutModal()" aria-label="Close logout confirmation"
                    class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:hover:bg-zinc-800 dark:hover:text-white dark:focus:ring-zinc-700">
                    <i class="ti ti-x text-lg" aria-hidden="true"></i>
                </button>

                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600 ring-8 ring-red-50/60 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-950/20">
                    <i class="ti ti-logout text-3xl" aria-hidden="true"></i>
                </div>

                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-red-600 dark:text-red-400">
                    End this session
                </p>
                <h2 id="logoutModalTitle" class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">
                    Are you sure you want to log out?
                </h2>
                <p id="logoutModalDescription" class="mx-auto mt-2 max-w-xs text-sm leading-6 text-slate-500 dark:text-zinc-400">
                    You’ll be signed out of the administration console and need to sign in again to manage workspace accounts.
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/80 p-5 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:justify-center sm:px-8 sm:py-5">
                <button id="cancelLogoutButton" type="button" onclick="closeLogoutModal()"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 dark:focus:ring-zinc-700">
                    Stay signed in
                </button>
                <button type="button" onclick="submitLogout()"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-500/25">
                    <i class="ti ti-logout text-base" aria-hidden="true"></i>
                    Log out
                </button>
            </div>
        </div>
    </div>
    <script>
        let logoutModalTrigger = null;

        function openLogoutModal(event) {
            event.preventDefault();

            const modal = document.getElementById('logoutModal');
            if (!modal) return;

            logoutModalTrigger = event.currentTarget;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('cancelLogoutButton')?.focus();
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            logoutModalTrigger?.focus();
            logoutModalTrigger = null;
        }

        function submitLogout() {
            document.getElementById('logout-form')?.submit();
        }

        document.addEventListener('keydown', function (event) {
            const modal = document.getElementById('logoutModal');
            if (!modal || modal.classList.contains('hidden')) return;

            if (event.key === 'Escape') {
                closeLogoutModal();
            } else if (event.key === 'Tab') {
                const focusable = [...modal.querySelectorAll('button:not([disabled])')];
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last?.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first?.focus();
                }
            }
        });
    </script>
    @if (session('success') || session('error') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @if (session('success'))
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'success',
                        title: @json(session('success')),
                        showConfirmButton: false,
                        backdrop: true,
                        timer: 3000,
                        timerProgressBar: true,
                        background: 'rgba(17,24,39,0.85)',
                        color: '#fff',
                    });
                @endif

                const adminErrors = [
                    ...@json((array) session('error', [])),
                    ...@json($errors->all()),
                ];
                adminErrors.forEach((error, index) => {
                    setTimeout(() => {
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'error',
                            title: error,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            background: 'rgba(17,24,39,0.85)',
                            color: '#fff',
                        });
                    }, index * 1000);
                });
            });
        </script>
    @endif
    <script>
        function setAdminTheme(mode) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
            document.documentElement.classList.toggle('dark', isDark);
            localStorage.setItem('theme', mode);

            const button = document.getElementById('adminThemeToggle');
            const icon = document.getElementById('adminThemeIcon');
            if (button && icon) {
                icon.className = isDark ? 'ti ti-moon text-lg' : 'ti ti-sun text-lg';
                button.setAttribute('aria-label', `Switch to ${isDark ? 'light' : 'dark'} theme`);
                button.title = `${isDark ? 'Light' : 'Dark'} theme`;
            }
        }

        function toggleAdminTheme() {
            setAdminTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
        }

        function setAdminSidebarOpen(isOpen) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            const menuButton = document.getElementById('adminMobileMenuButton');
            if (!sidebar || !overlay) return;

            if (window.matchMedia('(min-width: 1024px)').matches) isOpen = false;
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            overlay.classList.toggle('invisible', !isOpen);
            overlay.classList.toggle('opacity-0', !isOpen);
            overlay.classList.toggle('opacity-100', isOpen);
            overlay.setAttribute('aria-hidden', String(!isOpen));
            menuButton?.setAttribute('aria-expanded', String(isOpen));
        }

        function toggleAdminSidebar() {
            const sidebar = document.getElementById('sidebar');
            setAdminSidebarOpen(sidebar?.classList.contains('-translate-x-full'));
        }

        function closeAdminSidebar() {
            setAdminSidebarOpen(false);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'system';
            setAdminTheme(savedTheme);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
            if ((localStorage.getItem('theme') || 'system') === 'system') {
                setAdminTheme(event.matches ? 'dark' : 'light');
                localStorage.setItem('theme', 'system');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAdminSidebar();
        });
        window.addEventListener('resize', () => {
            if (window.matchMedia('(min-width: 1024px)').matches) closeAdminSidebar();
        });
    </script>
</body>
</html>
