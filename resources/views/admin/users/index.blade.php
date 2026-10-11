@extends('admin.layouts.app')

@section('title', 'User accounts')
@section('heading', 'User accounts')

@section('content')
    @if (session('error'))
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">
            <p class="font-semibold">Email delivery issue</p>
            <p class="mt-1">{{ session('error') }}</p>
        </div>
    @endif
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-violet-700 dark:text-violet-400">Account directory</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">User accounts</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">Create accounts and manage access, jewelry features, roles, and invoice templates.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700">
            <i class="ti ti-user-plus" aria-hidden="true"></i> Add account
        </a>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 border-b border-slate-100 p-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="text-sm text-slate-500 dark:text-zinc-400">{{ number_format($users->total()) }} {{ Str::plural('account', $users->total()) }}</p>
            <div class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" aria-label="Search accounts" placeholder="Search name, email, business"
                    class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:border-zinc-700 dark:bg-zinc-950 sm:w-72">
                <button class="rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Search</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[64rem] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">Account</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Jewelry Touch</th>
                        <th class="px-5 py-3">Invoice template</th>
                        <th class="px-5 py-3 text-right sm:px-6">Manage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="font-semibold text-slate-800 dark:text-zinc-100">{{ $user->full_name }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">{{ $user->email }}@if ($user->company_name) · {{ $user->company_name }}@endif</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $user->is_admin === 'Y' ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ $user->is_admin === 'Y' ? 'Administrator' : 'User' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold {{ $user->status === '1' ? 'text-emerald-700 dark:text-emerald-400' : ($user->status === '2' ? 'text-red-700 dark:text-red-400' : 'text-slate-500') }}">
                                {{ $user->status === '1' ? 'Active' : ($user->status === '2' ? 'Blocked' : 'Inactive') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 text-xs {{ (int) $user->is_jewelry === 1 ? 'font-semibold text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-zinc-400' }}">
                                    <i class="ti {{ (int) $user->is_jewelry === 1 ? 'ti-diamond' : 'ti-minus' }}" aria-hidden="true"></i>
                                    {{ (int) $user->is_jewelry === 1 ? 'Enabled' : 'Standard' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600 dark:text-zinc-300">{{ config("invoice.templates.{$user->invoice_template}.label", 'Default') }}</td>
                            <td class="px-5 py-4 text-right sm:px-6">
                                <a href="{{ route('admin.users.edit', $user->user_id) }}#password" class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-violet-200 px-3 text-xs font-semibold text-violet-700 transition hover:border-violet-300 hover:bg-violet-50 dark:border-violet-900 dark:text-violet-300 dark:hover:bg-violet-950/40">
                                    <i class="ti ti-mail-forward" aria-hidden="true"></i> Send email again
                                </a>
                                <a href="{{ route('admin.users.edit', $user->user_id) }}" class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:border-violet-300 hover:text-violet-700 dark:border-zinc-700 dark:text-zinc-200 dark:hover:border-violet-700 dark:hover:text-violet-300">
                                    <i class="ti ti-pencil" aria-hidden="true"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500 dark:text-zinc-400">No matching accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 dark:border-zinc-800 sm:px-6">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
