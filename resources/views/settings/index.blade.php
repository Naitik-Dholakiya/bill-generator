@extends('layouts.app')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

        @include('layout.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            @include('layout.navbar', ['title' => 'Settings'])
            <main class="flex-1 overflow-y-auto p-4 md:p-6">

                <div class="max-w-5xl mx-auto">

                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-semibold text-gray-900 dark:text-white">
                            Account Settings
                        </h1>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage your account security and preferences.
                        </p>
                    </div>

                    <div class="grid gap-6">

                        {{-- Security Card --}}
                        <div
                            class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">

                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Password & Security
                                    </h3>

                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        Update your password to keep your account secure.
                                    </p>
                                </div>

                                <button onclick="openModal('changePasswordModal')" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                                   bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium
                                   transition-all duration-200">

                                    <i class="ti ti-lock mr-2"></i>
                                    Change Password
                                </button>

                            </div>
                        </div>

                        {{-- Danger Zone --}}
                        <div
                            class="bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-900 rounded-2xl p-6 shadow-sm">

                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                <div>
                                    <h3 class="text-lg font-semibold text-red-600">
                                        Danger Zone
                                    </h3>

                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        Permanently delete your account and all associated data.
                                    </p>
                                </div>

                                <button onclick="openModal('deleteAccountModal')" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                                   bg-red-600 hover:bg-red-700 text-white text-sm font-medium
                                   transition-all duration-200">

                                    <i class="ti ti-trash mr-2"></i>
                                    Delete Account
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </main>
            @include('layout.footer')
        </div>
    </div>
    {{-- Change Password Modal --}}
    <div id="changePasswordModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">

    <div
        class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-800">

        <div class="p-6 border-b border-gray-200 dark:border-zinc-800">

            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Change Password
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Enter your new password below.
            </p>

        </div>

        <form action="{{ route('changePassword') }}" method="POST">

            @csrf

            <div class="p-6 space-y-4">

                <div>
                    <label class="block text-sm font-medium mb-2">
                        New Password
                    </label>

                    <input
                        type="password"
                        name="tb_password"
                        required
                        class="w-full rounded-xl border border-gray-300 dark:border-zinc-700
                               bg-white dark:bg-zinc-800 px-4 py-3
                               focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="tb_password_confirmation"
                        required
                        class="w-full rounded-xl border border-gray-300 dark:border-zinc-700
                               bg-white dark:bg-zinc-800 px-4 py-3
                               focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>

            </div>

            <div
                class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-zinc-800">

                <button
                    type="button"
                    onclick="closeModal('changePasswordModal')"
                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-zinc-700
                           hover:bg-gray-100 dark:hover:bg-zinc-800 transition">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700
                           text-white transition">

                    Update Password

                </button>

            </div>

        </form>

    </div>

</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>