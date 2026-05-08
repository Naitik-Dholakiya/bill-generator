@extends('app')

@section('content')

<div class="min-h-screen bg-black relative overflow-hidden">

    <!-- Background Glow -->
    <div class="absolute top-0 left-0 w-60 sm:w-80 lg:w-[500px] h-60 sm:h-80 lg:h-[500px] bg-purple-700/20 blur-3xl rounded-full"></div>

    <div class="absolute bottom-0 right-0 w-60 sm:w-80 lg:w-[500px] h-60 sm:h-80 lg:h-[500px] bg-cyan-600/20 blur-3xl rounded-full"></div>

    <!-- Main Wrapper -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-4 sm:px-6 lg:px-8">

        <!-- Main Container -->
        <div class="w-full max-w-6xl
            bg-white/10 backdrop-blur-xl border border-white/20
            rounded-3xl overflow-hidden shadow-2xl
            grid grid-cols-1 lg:grid-cols-2">

            <!-- LEFT SIDE -->
            <div class="hidden lg:flex flex-col justify-center relative overflow-hidden
                bg-gradient-to-br from-cyan-600/80 via-blue-700/70 to-purple-700/70
                p-10 xl:p-14">

                <!-- Decorative -->
                <div class="absolute top-10 left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>

                <div class="absolute bottom-10 right-10 w-52 h-52 bg-cyan-300/10 rounded-full blur-2xl"></div>

                <div class="relative z-10">

                    <h1 class="text-5xl font-extrabold text-white leading-tight">
                        Welcome <br>
                        Back!
                    </h1>

                    <p class="mt-5 text-gray-200 text-lg leading-relaxed">
                        Login to continue accessing your dashboard
                        with a clean, secure and modern experience.
                    </p>

                    <!-- Features -->
                    <div class="mt-10 space-y-5">

                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                                🔒
                            </div>

                            <div>
                                <h4 class="text-white font-semibold">
                                    Secure Login
                                </h4>

                                <p class="text-gray-300 text-sm">
                                    Protected authentication system
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                                ⚡
                            </div>

                            <div>
                                <h4 class="text-white font-semibold">
                                    Fast Access
                                </h4>

                                <p class="text-gray-300 text-sm">
                                    Smooth and responsive performance
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                                🎨
                            </div>

                            <div>
                                <h4 class="text-white font-semibold">
                                    Premium UI
                                </h4>

                                <p class="text-gray-300 text-sm">
                                    Modern glassmorphism interface
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="bg-black/40 flex items-center justify-center">

                <div class="w-full max-w-md px-5 py-8 sm:px-8 sm:py-10">

                    <!-- Mobile Logo -->
                    <div class="flex justify-center lg:hidden mb-6">

                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-8 h-8 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Heading -->
                    <div class="mb-6 text-center lg:text-left">

                        <h2 class="text-3xl sm:text-4xl font-bold text-white">
                            Login
                        </h2>

                        <p class="text-gray-400 mt-2 text-sm sm:text-base">
                            Welcome back! Please login to continue
                        </p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-4 text-green-400 text-sm bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm">
                                Email Address
                            </label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="Enter your email"
                                class="w-full px-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-gray-400 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-cyan-400 transition duration-300 @error('email') border-red-500 @enderror"
                            >

                            @error('email')
                                <p class="text-red-400 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm">
                                Password
                            </label>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                placeholder="Enter your password"
                                class="w-full px-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-gray-400 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-400 transition duration-300 @error('password') border-red-500 @enderror"
                            >

                            @error('password')
                                <p class="text-red-400 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remember + Forgot -->
                        <div class="flex items-center justify-between gap-4">

                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-white/20 bg-white/10 text-cyan-500 focus:ring-cyan-400"
                                >

                                Remember me
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-sm text-cyan-400 hover:text-cyan-300 transition">
                                    Forgot Password?
                                </a>
                            @endif
                        </div>

                        <!-- Button -->
                        <button
                            type="submit"
                            class="w-full py-3 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-600 text-white font-semibold text-sm sm:text-base hover:scale-[1.01] transition duration-300 shadow-lg hover:shadow-cyan-500/30"
                        >
                            Login
                        </button>

                        <!-- Register -->
                        <p class="text-center text-gray-400 text-sm pt-2">
                            Don’t have an account?
                            <a href="{{ route('register') }}"
                                class="text-cyan-400 hover:text-cyan-300 font-semibold">
                                Register
                            </a>
                        </p>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection