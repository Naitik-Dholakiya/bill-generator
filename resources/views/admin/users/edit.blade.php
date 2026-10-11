@extends('admin.layouts.app')

@section('title', 'Edit account')
@section('heading', 'Edit account')

@section('content')
    <div class="mb-7">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-violet-700 hover:underline dark:text-violet-300"><i class="ti ti-arrow-left mr-1" aria-hidden="true"></i>Back to accounts</a>
        <h2 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Manage account</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">Update profile, role, status, Touch access, or assigned invoice template.</p>
    </div>
    <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.users.form', ['submitLabel' => 'Save account changes'])
    </form>
@endsection
