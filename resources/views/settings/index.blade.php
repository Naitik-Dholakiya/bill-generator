@extends('layouts.app')

@section('content')
    @php
        $initials = collect(preg_split('/\s+/', trim($setting->full_name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $accountStatus = match ((string) $setting->status) {
            '0' => 'Inactive',
            '2' => 'Blocked',
            default => 'Active',
        };
        $statusStyle = match ((string) $setting->status) {
            '0' => 'border-slate-200 bg-slate-100 text-slate-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
            '2' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/70 dark:bg-red-950/40 dark:text-red-400',
            default => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-400',
        };
    @endphp

    <div class="flex h-screen overflow-hidden bg-slate-50 dark:bg-zinc-950">
        @include('layout.sidebar')

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Settings'])

            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="mb-8">
                        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">
                            Workspace
                        </p>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h1 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">
                                    Settings
                                </h1>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-zinc-400">
                                    Manage your personal information, business identity, and account security.
                                </p>
                            </div>
                            <span class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusStyle }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $setting->status === '1' ? 'bg-emerald-500' : ($setting->status === '2' ? 'bg-red-500' : 'bg-slate-400') }}"></span>
                                {{ $accountStatus }} account
                            </span>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div role="alert" class="mb-6 flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/30 dark:text-red-300">
                            <i class="ti ti-alert-circle mt-0.5 text-lg" aria-hidden="true"></i>
                            <div>
                                <p class="font-semibold">There’s something to review</p>
                                <p class="mt-1">Check the fields marked below and save your changes again.</p>
                            </div>
                        </div>
                    @endif

                    <section aria-label="Account profile" class="mb-7 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="h-1.5 bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-500"></div>
                        <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-7">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-indigo-600 text-xl font-bold text-white shadow-lg shadow-cyan-900/10">
                                {{ $initials ?: 'U' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-zinc-500">
                                    Personal account
                                </p>
                                <h2 class="mt-1 truncate text-xl font-semibold text-slate-900 dark:text-white">
                                    {{ $setting->full_name }}
                                </h2>
                                <p class="mt-1 truncate text-sm text-slate-500 dark:text-zinc-400">
                                    {{ $setting->email }}
                                </p>
                            </div>
                            <div class="hidden h-12 w-px bg-slate-200 dark:bg-zinc-800 sm:block"></div>
                            <div class="sm:min-w-44">
                                <p class="text-xs font-medium text-slate-400 dark:text-zinc-500">Business profile</p>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-700 dark:text-zinc-200">
                                    {{ $setting->company_name ?: 'Not set up yet' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <section aria-labelledby="personal-info-heading" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 dark:border-zinc-800 sm:px-7">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300">
                                    <i class="ti ti-user-circle text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h2 id="personal-info-heading" class="font-semibold text-slate-900 dark:text-white">
                                        Personal information
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                                        Keep your name and contact details up to date.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-x-6 gap-y-5 p-5 sm:grid-cols-2 sm:p-7">
                                <div>
                                    <label for="full_name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                                        Full name <span class="text-red-500">*</span>
                                    </label>
                                    <input id="full_name" name="full_name" type="text" required maxlength="100"
                                        autocomplete="name" value="{{ old('full_name', $setting->full_name) }}"
                                        aria-invalid="{{ $errors->has('full_name') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-white dark:focus:bg-zinc-800 @error('full_name') border-red-500 dark:border-red-500 @enderror">
                                    @error('full_name')
                                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                                        Email address <span class="text-red-500">*</span>
                                    </label>
                                    <input id="email" name="email" type="email" required maxlength="100"
                                        autocomplete="email" value="{{ old('email', $setting->email) }}"
                                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-white dark:focus:bg-zinc-800 @error('email') border-red-500 dark:border-red-500 @enderror">
                                    @error('email')
                                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="phone" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                                        Phone number <span class="font-normal text-slate-400">(optional)</span>
                                    </label>
                                    <div class="relative">
                                        <i class="ti ti-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                                        <input id="phone" name="phone" type="tel" maxlength="15"
                                            autocomplete="tel" value="{{ old('phone', $setting->phone) }}"
                                            aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-white dark:focus:bg-zinc-800 @error('phone') border-red-500 dark:border-red-500 @enderror">
                                    </div>
                                    @error('phone')
                                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section aria-labelledby="business-info-heading" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 dark:border-zinc-800 sm:px-7">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300">
                                    <i class="ti ti-building-store text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h2 id="business-info-heading" class="font-semibold text-slate-900 dark:text-white">
                                        Business information
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                                        Add the business details associated with your account.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-x-6 gap-y-5 p-5 sm:grid-cols-2 sm:p-7">
                                <div>
                                    <label for="company_name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                                        Business name
                                    </label>
                                    <input id="company_name" name="company_name" type="text" maxlength="150"
                                        autocomplete="organization" value="{{ old('company_name', $setting->company_name) }}"
                                        aria-invalid="{{ $errors->has('company_name') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-white dark:focus:bg-zinc-800 @error('company_name') border-red-500 dark:border-red-500 @enderror">
                                    @error('company_name')
                                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="company_gstin" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                                        GSTIN <span class="font-normal text-slate-400">(optional)</span>
                                    </label>
                                    <div class="relative">
                                        <i class="ti ti-id absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                                        <input id="company_gstin" name="company_gstin" type="text" maxlength="15"
                                            value="{{ old('company_gstin', $setting->company_gstin) }}"
                                            aria-invalid="{{ $errors->has('company_gstin') ? 'true' : 'false' }}"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm uppercase text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-white dark:focus:bg-zinc-800 @error('company_gstin') border-red-500 dark:border-red-500 @enderror">
                                    </div>
                                    @error('company_gstin')
                                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="company_address" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                                        Business address
                                    </label>
                                    <textarea id="company_address" name="company_address" rows="4"
                                        autocomplete="street-address" aria-invalid="{{ $errors->has('company_address') ? 'true' : 'false' }}"
                                        class="w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-white dark:focus:bg-zinc-800 @error('company_address') border-red-500 dark:border-red-500 @enderror">{{ old('company_address', $setting->company_address) }}</textarea>
                                    @error('company_address')
                                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-slate-400 dark:text-zinc-500">
                                Your information is only used to manage your billing account.
                            </p>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-900/15 dark:bg-cyan-600 dark:hover:bg-cyan-500 dark:focus:ring-cyan-500/20">
                                <i class="ti ti-device-floppy text-base" aria-hidden="true"></i>
                                Save changes
                            </button>
                        </div>
                    </form>

                    <section aria-labelledby="security-heading" class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                    <i class="ti ti-shield-lock text-xl" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h2 id="security-heading" class="font-semibold text-slate-900 dark:text-white">
                                        Password & security
                                    </h2>
                                    <p class="mt-1 max-w-xl text-sm leading-6 text-slate-500 dark:text-zinc-400">
                                        Keep your account protected by choosing a strong password and updating it regularly.
                                    </p>
                                </div>
                            </div>
                            <button type="button" onclick="openModal('changePasswordModal')"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-900/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <i class="ti ti-key text-base" aria-hidden="true"></i>
                                Change password
                            </button>
                        </div>
                    </section>

                    <p class="py-7 text-center text-xs text-slate-400 dark:text-zinc-600">
                        Need help with your account? Contact your workspace administrator.
                    </p>
                </div>
            </main>
            @include('layout.footer')
        </div>
    </div>

    <div id="changePasswordModal" role="dialog" aria-modal="true" aria-labelledby="changePasswordTitle"
        class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        onclick="if (event.target === this) closeModal('changePasswordModal')">
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between border-b border-slate-100 p-6 dark:border-zinc-800">
                <div>
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-300">
                        <i class="ti ti-lock text-lg" aria-hidden="true"></i>
                    </div>
                    <h2 id="changePasswordTitle" class="text-lg font-semibold text-slate-900 dark:text-white">
                        Change password
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                        Enter and confirm your new password.
                    </p>
                </div>
                <button type="button" onclick="closeModal('changePasswordModal')" aria-label="Close dialog"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-zinc-800 dark:hover:text-white">
                    <i class="ti ti-x text-lg" aria-hidden="true"></i>
                </button>
            </div>

            <form action="{{ route('changePassword') }}" method="POST">
                @csrf
                <div class="space-y-4 p-6">
                    <div>
                        <label for="tb_password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                            New password
                        </label>
                        <input id="tb_password" type="password" name="tb_password" required minlength="6"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                    <div>
                        <label for="tb_password_confirmation" class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-300">
                            Confirm new password
                        </label>
                        <input id="tb_password_confirmation" type="password" name="tb_password_confirmation" required minlength="6"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 p-5 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal('changePasswordModal')"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 focus:outline-none focus:ring-4 focus:ring-cyan-500/20">
                        <i class="ti ti-check text-base" aria-hidden="true"></i>
                        Update password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            modal.querySelector('input')?.focus();
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeModal('changePasswordModal');
        });
    </script>
@endsection
