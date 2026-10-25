@extends('layouts.app')

@section('title', 'Create Supplier Bill')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => 'Create Supplier Bill'])
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Supplier Bill</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Record products purchased from a supplier.</p>
                </div>

                @if ($suppliers->isEmpty() || $products->isEmpty())
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                        Add an active supplier and at least one active product before creating a bill.
                        @if ($suppliers->isEmpty())
                            <a href="{{ route('createSupplier') }}" class="ml-1 font-semibold underline">Add supplier</a>
                        @endif
                        @if ($products->isEmpty())
                            <a href="{{ route('products.create') }}" class="ml-1 font-semibold underline">Add product</a>
                        @endif
                    </div>
                @endif

                @include('supplier-bills.form', [
                    'action' => route('supplier-bills.store'),
                    'isEdit' => false,
                    'bill' => null,
                ])
            </main>
            @include('layout.footer')
        </div>
    </div>
@endsection
