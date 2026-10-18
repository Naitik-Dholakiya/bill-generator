<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Billing System'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>.swal2-timer-progress-bar{background:#accf9c!important}</style>

    @stack('styles')
</head>

<body class="h-screen overflow-hidden bg-gray-50 font-sans text-gray-900 dark:bg-zinc-950 dark:text-gray-100">

    @yield('content')

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
                    You’ll be signed out of your billing workspace and need to sign in again to continue.
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

    @if (session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                backdrop: true,
                timer: 3000,
                timerProgressBar: true,
                background: 'rgba(17,24,39,0.85)',
                color: '#fff',
            });
        </script>
    @endif

    {{-- Error Messages --}}
    @if (session('error'))
        <script>
            const errorSession = @json(session('error'));
            const errors = Array.isArray(errorSession) ? errorSession : [errorSession];

            errors.forEach((error, index) => {
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
                        color: '#fff'
                    });
                }, index * 1000);
            });
        </script>
    @endif

    {{-- Warning Message --}}
    @if (session('warning'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'warning',
                title: '{{ session('warning') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#111827',
                color: '#fff'
            });
        </script>
    @endif
    {{-- Info Message --}}
    @if (session('info'))
        <script>
            Swal.fire({
                toast: true,
                position: 'center',
                icon: 'info',
                title: '{{ session('info') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#111827',
                color: '#fff'
            });
        </script>
    @endif
    <script>
        // ── Theme ──────────────────────────────────────────────────────────────────

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

        const THEME_ICONS = {
            light: 'ti ti-sun text-base',
            dark: 'ti ti-moon text-base',
            system: 'ti ti-device-laptop text-base',
        };

        function setTheme(mode) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
            document.documentElement.classList.toggle('dark', isDark);
            localStorage.setItem('theme', mode);
            updateThemeIcon(mode);
            closeThemeMenu();
        }

        function updateThemeIcon(mode) {
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = THEME_ICONS[mode] ?? THEME_ICONS.light;
        }

        function toggleThemeMenu() {
            const menu = document.getElementById('themeMenu');
            const btn = document.getElementById('themeToggleBtn');
            if (!menu) return;
            const opening = menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !opening);
            btn?.setAttribute('aria-expanded', String(opening));
        }

        function closeThemeMenu() {
            const menu = document.getElementById('themeMenu');
            const btn = document.getElementById('themeToggleBtn');
            menu?.classList.add('hidden');
            btn?.setAttribute('aria-expanded', 'false');
        }

        function toggleAccountMenu() {
            const menu = document.getElementById('accountMenu');
            const button = document.getElementById('accountMenuToggle');
            if (!menu) return;

            const opening = menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !opening);
            button?.setAttribute('aria-expanded', String(opening));
        }

        function closeAccountMenu() {
            document.getElementById('accountMenu')?.classList.add('hidden');
            document.getElementById('accountMenuToggle')?.setAttribute('aria-expanded', 'false');
        }

        // Close on outside click
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('themeWrapper');
            if (wrapper && !wrapper.contains(e.target)) closeThemeMenu();
            const accountWrapper = document.getElementById('accountMenuWrapper');
            if (accountWrapper && !accountWrapper.contains(e.target)) closeAccountMenu();
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeThemeMenu();
                closeAccountMenu();
                closeMobileSidebar();
            }

            const modal = document.getElementById('logoutModal');
            if (e.key === 'Tab' && modal && !modal.classList.contains('hidden')) {
                const focusable = [...modal.querySelectorAll('button:not([disabled])')];
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last?.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first?.focus();
                }
            }
        });

        window.addEventListener('resize', function() {
            if (window.matchMedia('(min-width: 1024px)').matches) closeMobileSidebar();
        });

        // ── Mobile sidebar ─────────────────────────────────────────────────────────

        function setMobileSidebarOpen(isOpen) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            const menuButton = document.getElementById('mobileMenuButton');

            if (!sidebar || !overlay) return;
            if (window.matchMedia('(min-width: 1024px)').matches) isOpen = false;

            sidebar.classList.toggle('-translate-x-full', !isOpen);
            overlay.classList.toggle('invisible', !isOpen);
            overlay.classList.toggle('opacity-0', !isOpen);
            overlay.classList.toggle('opacity-100', isOpen);
            overlay.setAttribute('aria-hidden', String(!isOpen));
            menuButton?.setAttribute('aria-expanded', String(isOpen));
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            setMobileSidebarOpen(sidebar.classList.contains('-translate-x-full'));
        }

        function closeMobileSidebar() {
            setMobileSidebarOpen(false);
        }

        // ── Active nav item ────────────────────────────────────────────────────────

        function setActive(e, el) {
            e.preventDefault();
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active', 'bg-gray-100', 'dark:bg-zinc-800',
                    'text-gray-900', 'dark:text-white', 'font-medium');
            });
            el.classList.add('active', 'bg-gray-100', 'dark:bg-zinc-800',
                'text-gray-900', 'dark:text-white', 'font-medium');
            const title = document.getElementById('pageTitle');
            if (title) title.textContent = el.querySelector('span')?.textContent.trim() ??
                el.textContent.trim();
        }

        // ── Init on DOM ready ──────────────────────────────────────────────────────

        document.addEventListener('DOMContentLoaded', function() {
            const saved = localStorage.getItem('theme') || 'system';
            setTheme(saved);
        });
    </script>

    @stack('scripts')

</body>

</html>
