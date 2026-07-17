<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Billing System'))</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        body {
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Smooth Theme Transition */
        *,
        *::before,
        *::after {
            transition:
                background-color 250ms ease,
                border-color 250ms ease,
                color 250ms ease,
                fill 250ms ease,
                stroke 250ms ease,
                box-shadow 250ms ease;
        }

        .swal2-timer-progress-bar {
            background: #accf9c !important;
        }
    </style>

    @stack('styles')
</head>

<body class="h-screen bg-gray-50 dark:bg-zinc-950 text-gray-900 dark:text-gray-100 overflow-hidden">

    @yield('content')
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
            const errors = @json(session('error'));

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
                }, index * 1000); // Show next after previous finishes
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

        // Close on outside click
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('themeWrapper');
            if (wrapper && !wrapper.contains(e.target)) closeThemeMenu();
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeThemeMenu();
        });

        // ── Mobile sidebar ─────────────────────────────────────────────────────────

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar?.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
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
