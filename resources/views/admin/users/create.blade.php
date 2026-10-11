@extends('admin.layouts.app')

@section('title', 'Add user')
@section('heading', 'Add user')

@section('content')
    <div class="mb-7">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-violet-700 hover:underline dark:text-violet-300"><i class="ti ti-arrow-left mr-1" aria-hidden="true"></i>Back to accounts</a>
        <h2 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Create a workspace account</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">Set up the account, its access role, and default invoice capabilities.</p>
    </div>
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        @include('admin.users.form', ['submitLabel' => 'Create account'])
    </form>
@endsection
