@extends('layouts.app')

@section('title', $title . ' · Billora')

@section('content')
    <div class="flex h-screen flex-col overflow-hidden bg-slate-50 dark:bg-zinc-950">
        <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 dark:border-zinc-800 dark:bg-zinc-900 sm:px-8">
            <a href="{{ route('login.form') }}" class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-indigo-600 font-bold text-white">B</span>
                <span>
                    <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-white">Billora</span>
                    <span class="block text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">Billing workspace</span>
                </span>
            </a>
            <a href="{{ route('login.form') }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <i class="ti ti-login" aria-hidden="true"></i> Sign in
            </a>
        </header>

        <main class="flex-1 overflow-y-auto px-4 py-8 sm:px-8 sm:py-12">
            <article class="mx-auto max-w-4xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">Billora resources</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ $title }}</h1>
                <p class="mt-2 border-b border-slate-100 pb-6 text-sm text-slate-500 dark:border-zinc-800 dark:text-zinc-400">{{ $description }}</p>
                <div class="document-content mt-7 text-sm leading-7 text-slate-700 dark:text-zinc-300">
                    {!! $content !!}
                </div>
            </article>
        </main>

        @include('layout.footer')
    </div>
@endsection
