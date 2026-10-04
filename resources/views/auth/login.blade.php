@extends('layouts.app')

@php
    // Change the product name here (one place only)
    $brand   = 'Billora';
    $tagline = 'CRM & Billing';
@endphp

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .login-page { font-family: 'Schibsted Grotesk', ui-sans-serif, system-ui, sans-serif; }
    .login-page .num { font-variant-numeric: tabular-nums; }

    /* One deliberate motion: the revenue line draws itself once on load */
    .login-page .spark {
        stroke-dasharray: 1;
        stroke-dashoffset: 1;
        animation: sparkDraw 1.6s cubic-bezier(.22,.61,.36,1) .35s forwards;
    }
    @keyframes sparkDraw { to { stroke-dashoffset: 0; } }
    @media (prefers-reduced-motion: reduce) {
        .login-page .spark { animation: none; stroke-dashoffset: 0; }
        .login-page * { transition-duration: 0.01ms !important; }
    }
</style>

<div class="login-page min-h-screen bg-black text-white grid grid-cols-1 lg:grid-cols-[1.15fr_1fr]">

    <!-- ============ LEFT: product preview (desktop) ============ -->
    <aside class="hidden lg:flex relative flex-col overflow-hidden
        bg-gradient-to-br from-cyan-600/80 via-blue-700/70 to-purple-700/70
        p-12 xl:p-16">

        <div class="absolute top-10 left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-cyan-300/10 rounded-full blur-3xl"></div>

        <!-- Brand -->
        <div class="relative z-10 flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-white/15 border border-white/25 backdrop-blur">
                <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 19V13M12 19V6M19 19V10"/>
                </svg>
            </span>
            <div class="leading-tight">
                <p class="text-xl font-bold">{{ $brand }}</p>
                <p class="text-sm text-gray-200">{{ $tagline }}</p>
            </div>
        </div>

        <!-- Headline -->
        <div class="relative z-10 mt-14 max-w-lg">
            <h1 class="text-4xl xl:text-5xl font-extrabold leading-[1.08] tracking-tight">
                Every client, quote and invoice in one place.
            </h1>
            <p class="mt-4 text-lg leading-relaxed text-gray-100/90">
                Follow leads, send invoices and see who has paid, without switching tools.
            </p>
        </div>

        <!-- Dashboard preview, bleeds off the panel edge (decorative) -->
        <div class="relative z-10 mt-12 -mr-44 -mb-28 w-[660px] max-w-none rounded-2xl border border-white/20 bg-black/45 p-6 shadow-2xl backdrop-blur-xl" aria-hidden="true">

            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-200">Dashboard</p>
                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs text-gray-200">This month</span>
            </div>

            <!-- Revenue -->
            <div class="mt-5 flex items-end justify-between gap-6">
                <div>
                    <p class="text-sm text-gray-400">Revenue collected</p>
                    <div class="mt-1 flex items-center gap-3">
                        <p class="num text-4xl font-extrabold">₹4,86,200</p>
                        <span class="rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs font-bold text-emerald-400">+12.4%</span>
                    </div>
                </div>

                <svg viewBox="0 0 240 60" class="h-16 w-56 shrink-0" fill="none">
                    <defs>
                        <linearGradient id="sparkStroke" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0" stop-color="#22d3ee"/>
                            <stop offset="0.55" stop-color="#3b82f6"/>
                            <stop offset="1" stop-color="#a855f7"/>
                        </linearGradient>
                        <linearGradient id="sparkFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#3b82f6" stop-opacity="0.28"/>
                            <stop offset="1" stop-color="#3b82f6" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0 48 C20 44 30 40 50 42 S80 30 100 28 S130 34 150 22 S190 16 210 10 S230 8 240 4 L240 60 L0 60 Z" fill="url(#sparkFill)"/>
                    <path class="spark" pathLength="1" d="M0 48 C20 44 30 40 50 42 S80 30 100 28 S130 34 150 22 S190 16 210 10 S230 8 240 4" stroke="url(#sparkStroke)" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">

                <!-- Invoices -->
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-semibold text-gray-200">Recent invoices</p>
                    <div class="mt-3 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate font-medium">Sharma Traders</p>
                                <span class="text-xs text-emerald-400">Paid</span>
                            </div>
                            <span class="num font-semibold">₹48,000</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate font-medium">Patel Logistics</p>
                                <span class="text-xs text-amber-300">Due in 3 days</span>
                            </div>
                            <span class="num font-semibold">₹1,26,500</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate font-medium">Nova Interiors</p>
                                <span class="text-xs text-red-400">Overdue</span>
                            </div>
                            <span class="num font-semibold">₹32,800</span>
                        </div>
                    </div>
                </div>

                <!-- Pipeline -->
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-semibold text-gray-200">Client pipeline</p>
                    <div class="mt-3 space-y-3 text-sm">
                        <div>
                            <div class="flex justify-between"><span class="text-gray-300">Leads</span><span class="num font-semibold">24</span></div>
                            <div class="mt-1.5 h-2 rounded-full bg-white/10"><div class="h-full w-full rounded-full bg-cyan-400"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between"><span class="text-gray-300">Proposals sent</span><span class="num font-semibold">11</span></div>
                            <div class="mt-1.5 h-2 rounded-full bg-white/10"><div class="h-full w-[46%] rounded-full bg-blue-500"></div></div>
                        </div>
                        <div>
                            <div class="flex justify-between"><span class="text-gray-300">Won</span><span class="num font-semibold">7</span></div>
                            <div class="mt-1.5 h-2 rounded-full bg-white/10"><div class="h-full w-[29%] rounded-full bg-purple-500"></div></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </aside>

    <!-- ============ RIGHT: sign in ============ -->
    <main class="relative flex flex-col overflow-hidden px-5 py-8 sm:px-10">

        <div class="absolute top-0 left-0 w-60 sm:w-80 h-60 sm:h-80 bg-purple-700/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-60 sm:w-80 h-60 sm:h-80 bg-cyan-600/20 blur-3xl rounded-full"></div>

        <div class="relative z-10 flex flex-1 items-center justify-center">
            <div class="w-full max-w-sm">

                <!-- Mobile brand -->
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-600 shadow-lg shadow-cyan-500/30">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 19V13M12 19V6M19 19V10"/>
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <p class="text-xl font-bold">{{ $brand }}</p>
                        <p class="text-sm text-gray-400">{{ $tagline }}</p>
                    </div>
                </div>

                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Welcome back</h2>
                <p class="mt-2 text-gray-400">Sign in to your {{ $brand }} workspace.</p>

                @if (session('status'))
                    <div class="mt-6 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-400" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-6 flex gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm" class="mt-8 space-y-5" novalidate>
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="tb_email" class="mb-2 block text-sm font-medium text-gray-200">Email</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input
                                id="tb_email"
                                type="email"
                                name="tb_email"
                                value="{{ old('tb_email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="you@company.com"
                                @error('tb_email') aria-invalid="true" @enderror
                                class="w-full rounded-xl border bg-white/10 py-3 pl-12 pr-4 text-sm text-white placeholder-gray-500 transition duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400 sm:text-base @error('tb_email') border-red-500 @else border-white/20 @enderror"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="tb_password" class="mb-2 block text-sm font-medium text-gray-200">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input
                                id="tb_password"
                                type="password"
                                name="tb_password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                @error('tb_password') aria-invalid="true" @enderror
                                class="w-full rounded-xl border bg-white/10 py-3 pl-12 pr-12 text-sm text-white placeholder-gray-500 transition duration-300 focus:outline-none focus:ring-2 focus:ring-purple-400 sm:text-base @error('tb_password') border-red-500 @else border-white/20 @enderror"
                            >
                            <button
                                type="button"
                                id="togglePassword"
                                aria-label="Show password"
                                aria-controls="tb_password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-4 text-gray-400 transition hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-400"
                            >
                                <svg id="eyeOn" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eyeOff" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p id="capsHint" class="mt-2 hidden text-sm text-amber-300" role="status">Caps Lock is on.</p>
                    </div>

                    <!-- Remember -->
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-gray-300">
                        <input
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded border-white/20 bg-white/10 text-cyan-500 focus:ring-cyan-400"
                        >
                        Keep me signed in on this device
                    </label>

                    <!-- Submit -->
                    <button
                        type="submit"
                        id="submitBtn"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-600 py-3.5 text-sm font-semibold text-white shadow-lg transition duration-300 hover:scale-[1.01] hover:shadow-cyan-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-300 disabled:cursor-not-allowed disabled:opacity-80 disabled:hover:scale-100 sm:text-base"
                    >
                        <svg id="spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                            <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                        <span id="submitLabel">Sign in</span>
                    </button>
                </form>

                <p class="mt-8 text-sm text-gray-400">
                    Can't sign in? Ask your workspace admin to reset your access.
                </p>
            </div>
        </div>

        <p class="relative z-10 mt-8 text-center text-xs text-gray-500 lg:text-left">
            &copy; {{ date('Y') }} {{ $brand }}
        </p>
    </main>
</div>

<script>
    (function () {
        var form = document.getElementById('loginForm');
        var input = document.getElementById('tb_password');
        var toggle = document.getElementById('togglePassword');
        var eyeOn = document.getElementById('eyeOn');
        var eyeOff = document.getElementById('eyeOff');
        var caps = document.getElementById('capsHint');
        var btn = document.getElementById('submitBtn');
        var spinner = document.getElementById('spinner');
        var label = document.getElementById('submitLabel');

        // Show / hide password
        toggle.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eyeOn.classList.toggle('hidden', show);
            eyeOff.classList.toggle('hidden', !show);
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });

        // Caps Lock hint
        function checkCaps(e) {
            if (e.getModifierState) caps.classList.toggle('hidden', !e.getModifierState('CapsLock'));
        }
        input.addEventListener('keyup', checkCaps);
        input.addEventListener('keydown', checkCaps);
        input.addEventListener('blur', function () { caps.classList.add('hidden'); });

        // Loading state on submit (prevents double submits)
        function resetBtn() {
            btn.disabled = false;
            spinner.classList.add('hidden');
            label.textContent = 'Sign in';
        }
        form.addEventListener('submit', function () {
            btn.disabled = true;
            spinner.classList.remove('hidden');
            label.textContent = 'Signing in…';
        });
        window.addEventListener('pageshow', resetBtn);
    })();
</script>

@endsection