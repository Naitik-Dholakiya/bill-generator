<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Register Page</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6">

    <!-- Main Container -->
    <div class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden grid lg:grid-cols-2">

        <!-- Left Side -->
        <div class="hidden lg:flex flex-col justify-center bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 text-white p-16 relative">

            <div class="absolute inset-0 bg-black/10"></div>

            <div class="relative z-10">
                <h1 class="text-5xl font-bold leading-tight mb-6">
                    Create Your Account
                </h1>

                <p class="text-lg text-blue-100 leading-relaxed mb-10">
                    Join thousands of professionals managing their business,
                    analytics, and workflow with our modern platform.
                </p>

                <!-- Features -->
                <div class="space-y-5">

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                            ✓
                        </div>
                        <span class="text-lg">Secure authentication</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                            ✓
                        </div>
                        <span class="text-lg">Modern dashboard access</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                            ✓
                        </div>
                        <span class="text-lg">Fast & responsive experience</span>
                    </div>

                </div>
            </div>

        </div>

        <!-- Right Side -->
        <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center">

            <!-- Logo -->
            <div class="mb-10">
                <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg">
                    A
                </div>
            </div>

            <!-- Heading -->
            <div class="mb-8">
                <h2 class="text-4xl font-bold text-gray-900 mb-3">
                    Register
                </h2>

                <p class="text-gray-500">
                    Please enter your details to create your account.
                </p>
            </div>

            <!-- Form -->
            <form method="POST" action="/register" class="space-y-6">

                @csrf

                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="John Doe"
                        class="w-full px-5 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="john@example.com"
                        class="w-full px-5 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        class="w-full px-5 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
                    >
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        class="w-full px-5 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition"
                    >
                </div>

                <!-- Terms -->
                <div class="flex items-start gap-3">
                    <input
                        type="checkbox"
                        class="mt-1 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                    >

                    <p class="text-sm text-gray-500">
                        I agree to the
                        <a href="#" class="text-blue-600 hover:underline">
                            Terms & Conditions
                        </a>
                        and
                        <a href="#" class="text-blue-600 hover:underline">
                            Privacy Policy
                        </a>
                    </p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl shadow-lg hover:shadow-xl transition duration-300"
                >
                    Create Account
                </button>

            </form>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>

                <div class="relative flex justify-center">
                    <span class="bg-white px-4 text-sm text-gray-400">
                        OR CONTINUE WITH
                    </span>
                </div>
            </div>

            <!-- Social Login -->
            <div class="grid grid-cols-2 gap-4">

                <button
                    class="flex items-center justify-center gap-3 border border-gray-300 py-3 rounded-xl hover:bg-gray-50 transition"
                >
                    <img
                        src="https://www.svgrepo.com/show/475656/google-color.svg"
                        class="w-5 h-5"
                    >
                    Google
                </button>

                <button
                    class="flex items-center justify-center gap-3 border border-gray-300 py-3 rounded-xl hover:bg-gray-50 transition"
                >
                    <img
                        src="https://www.svgrepo.com/show/448224/facebook.svg"
                        class="w-5 h-5"
                    >
                    Facebook
                </button>

            </div>

            <!-- Login -->
            <p class="text-center text-gray-500 text-sm mt-10">
                Already have an account?
                <a href="/login" class="text-blue-600 font-semibold hover:underline">
                    Login
                </a>
            </p>

        </div>

    </div>

</body>
</html>