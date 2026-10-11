@extends('layouts.app')

@section('content')
    <div class="flex h-screen overflow-hidden bg-slate-50 dark:bg-zinc-950">
        @include('layout.sidebar')

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Jewelry accounts'])

            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                    <div class="mb-8">
                        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">
                            Administration
                        </p>
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">
                            Jewelry invoice access
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-zinc-400">
                            Enable Touch fields for jewelry accounts. Other accounts continue to use standard products and invoices.
                        </p>
                    </div>

                    @if (session('success'))
                        <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/70 dark:bg-emerald-950/30 dark:text-emerald-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/30 dark:text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-slate-100 px-5 py-5 dark:border-zinc-800 sm:px-7">
                            <h2 class="font-semibold text-slate-900 dark:text-white">User accounts</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Only administrators can change this setting.</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[40rem] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 sm:px-7">Account</th>
                                        <th scope="col" class="px-5 py-3">Business</th>
                                        <th scope="col" class="px-5 py-3">Jewelry access</th>
                                        <th scope="col" class="px-5 py-3 text-right sm:px-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                    @forelse ($users as $user)
                                        <tr>
                                            <td class="px-5 py-4 sm:px-7">
                                                <p class="font-semibold text-slate-800 dark:text-zinc-100">{{ $user->full_name }}</p>
                                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">{{ $user->email }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-slate-600 dark:text-zinc-300">
                                                {{ $user->company_name ?: '—' }}
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $user->is_jewelry ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $user->is_jewelry ? 'bg-amber-500' : 'bg-slate-400' }}"></span>
                                                    {{ $user->is_jewelry ? 'Enabled' : 'Standard' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-right sm:px-7">
                                                <form action="{{ route('admin.jewelry-users.update', $user->user_id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_jewelry" value="{{ $user->is_jewelry ? 0 : 1 }}">
                                                    <button type="submit" class="rounded-xl px-3 py-2 text-xs font-semibold transition {{ $user->is_jewelry ? 'border border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800' : 'bg-cyan-600 text-white hover:bg-cyan-700' }}">
                                                        {{ $user->is_jewelry ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-zinc-400">
                                                No user accounts are available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($users->hasPages())
                            <div class="border-t border-slate-100 px-5 py-4 dark:border-zinc-800 sm:px-7">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </section>
                </div>
            </main>
        </div>
    </div>
@endsection
