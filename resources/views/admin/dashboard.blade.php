@extends('admin.layouts.app')

@section('title', 'Overview')
@section('heading', 'Overview')

@section('content')
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-violet-700 dark:text-violet-400">Platform overview</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Administration dashboard</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">Manage accounts and workspace access across the platform.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700">
            <i class="ti ti-user-plus" aria-hidden="true"></i> Add user
        </a>
    </div>

    <section aria-label="Account metrics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'User workspaces', 'value' => $metrics['users'], 'icon' => 'ti-users', 'color' => 'cyan'],
            ['label' => 'Active workspaces', 'value' => $metrics['active'], 'icon' => 'ti-circle-check', 'color' => 'emerald'],
            ['label' => 'Jewelry enabled', 'value' => $metrics['jewelry'], 'icon' => 'ti-diamond', 'color' => 'amber'],
            ['label' => 'Active admins', 'value' => $metrics['admins'], 'icon' => 'ti-shield-check', 'color' => 'violet'],
        ] as $metric)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">{{ $metric['label'] }}</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $metric['color'] }}-50 text-{{ $metric['color'] }}-700 dark:bg-{{ $metric['color'] }}-950/40 dark:text-{{ $metric['color'] }}-300">
                        <i class="ti {{ $metric['icon'] }} text-lg" aria-hidden="true"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ number_format($metric['value']) }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">Recently added workspaces</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Latest user accounts and their assigned capabilities.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-violet-700 hover:text-violet-800 dark:text-violet-300">Manage accounts <span aria-hidden="true">→</span></a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">Workspace</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Jewelry</th><th class="px-5 py-3">Invoice template</th><th class="px-5 py-3 text-right"> </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse ($recentUsers as $user)
                        <tr>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="font-semibold text-slate-800 dark:text-zinc-100">{{ $user->company_name ?: $user->full_name }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">{{ $user->email }} · {{ $user->full_name }}</p>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold {{ $user->status === '1' ? 'text-emerald-700 dark:text-emerald-400' : ($user->status === '2' ? 'text-red-700 dark:text-red-400' : 'text-slate-500') }}">
                                {{ $user->status === '1' ? 'Active' : ($user->status === '2' ? 'Blocked' : 'Inactive') }}
                            </td>
                            <td class="px-5 py-4 text-xs">{{ (int) $user->is_jewelry === 1 ? 'Enabled' : 'Standard' }}</td>
                            <td class="px-5 py-4 text-xs text-slate-600 dark:text-zinc-300">{{ config("invoice.templates.{$user->invoice_template}.label", 'Default') }}</td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.users.edit', $user->user_id) }}" class="font-semibold text-violet-700 hover:underline dark:text-violet-300">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-zinc-400">No user workspaces yet. Add the first account to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
