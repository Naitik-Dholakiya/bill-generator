@extends('layouts.app')

@section('title', $isJewelryUser ? 'Edit Jewellery Supplier Bill' : 'Edit Supplier Bill')

@section('content')
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">
        @include('layout.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('layout.navbar', ['title' => $isJewelryUser ? 'Edit Jewellery Supplier Bill' : 'Edit Supplier Bill'])
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $isJewelryUser ? 'Edit Jewellery Supplier Bill' : 'Edit Supplier Bill' }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $isJewelryUser ? 'Update jewellery weights, gold details and supplier information.' : 'Update bill details and purchased quantities.' }}</p>
                </div>

                @include('supplier-bills.form', [
                    'action' => route('supplier-bills.update', $bill->purchase_id),
                    'isEdit' => true,
                    'bill' => $bill,
                ])
            </main>
            @include('layout.footer')
        </div>
    </div>
@endsection
