@php
    $year = date('Y');
    $appName = config('app.name', 'Billing System');
@endphp

<footer class="shrink-0 border-t border-slate-200 bg-white px-4 py-3.5 dark:border-zinc-800 dark:bg-zinc-900 sm:px-6">
    <div class="mx-auto flex max-w-screen-2xl flex-col items-center justify-between gap-2.5 sm:flex-row sm:gap-4">
        <div class="flex min-w-0 flex-wrap items-center justify-center gap-x-2 gap-y-1 text-xs text-slate-500 dark:text-zinc-400 sm:justify-start">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-indigo-600 text-[10px] font-bold text-white" aria-hidden="true">V</span>
            <span>&copy; {{ $year }}</span>
            <span class="font-semibold text-slate-700 dark:text-zinc-200">{{ $appName }}</span>
            <span class="hidden text-slate-300 dark:text-zinc-700 sm:inline" aria-hidden="true">&middot;</span>
            <span class="hidden sm:inline">All rights reserved</span>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-[11px] font-medium text-slate-500 dark:text-zinc-400 sm:justify-end">
            <span>Billing workspace</span>
            <span class="hidden h-3 w-px bg-slate-200 dark:bg-zinc-700 sm:block" aria-hidden="true"></span>
            <span class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-semibold tracking-wide text-slate-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                VERSION 1.0.0
            </span>
        </div>
    </div>
</footer>
