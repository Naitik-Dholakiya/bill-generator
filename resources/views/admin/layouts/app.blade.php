<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') · Billora</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme') || 'system';
            const useDarkTheme = savedTheme === 'dark'
                || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', useDarkTheme);
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
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

        function confirmAdminLogout(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Sign out of administration?',
                text: 'You will need to sign in again to manage workspace accounts.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sign out',
                cancelButtonText: 'Stay signed in',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true,
                background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#18181b',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('adminLogoutForm').submit();
                }
            });
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
