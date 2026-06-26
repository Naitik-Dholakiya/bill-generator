@extends('layouts.app')

@section('title', 'Customers')

@section('content')

<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-zinc-950">

    @include('layout.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">

        @include('layout.navbar', ['title' => 'Customers'])

        <main class="flex-1 overflow-y-auto p-6">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Customer Management
                    </h1>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage all your customers from one place.
                    </p>
                </div>

                <a href="{{ route('createCustomer') }}"
                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl
                    bg-cyan-600 hover:bg-cyan-700
                    text-white font-medium shadow-lg shadow-cyan-500/20">

                    <i class="ti ti-plus"></i>
                    Add Customer
                </a>

            </div>

            <!-- Search -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 p-4 mb-6">

                <form method="GET">

                    <div class="relative">

                        <i class="ti ti-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <input type="text"
                            name="search"
                            placeholder="Search customers..."
                            class="w-full pl-11 pr-4 py-3 rounded-xl
                            bg-gray-50 dark:bg-zinc-800
                            border border-gray-200 dark:border-zinc-700
                            focus:ring-2 focus:ring-cyan-500
                            outline-none">

                    </div>

                </form>

            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50 dark:bg-zinc-800">

                            <tr>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Customer Code
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Customer Name
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Email
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold">
                                    Phone
                                </th>

                                <th class="px-6 py-4 text-center text-sm font-semibold">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">

                            @forelse($customers as $customer)

                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50">

                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="w-10 h-10 rounded-full bg-cyan-100 dark:bg-cyan-500/10 flex items-center justify-center">

                                                <i class="ti ti-user text-cyan-600"></i>

                                            </div>

                                            <div>

                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ $customer->customer_code }}
                                                </p>

                                            </div>

                                        </div>

                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $customer->customer_name }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $customer->email }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $customer->phone }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex items-center justify-center gap-2">

                                            <!-- View -->
                                            <a href="{{ route('viewCustomer', $customer->customer_id) }}"
    class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600
    hover:bg-blue-200 flex items-center justify-center">

    <i class="ti ti-eye"></i>
</a>

                                            <!-- Edit -->
                                            <!-- <a href="#"
                                                class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600
                                                hover:bg-amber-200 flex items-center justify-center">

                                                <i class="ti ti-edit"></i>

                                            </a> -->

                                            <!-- Delete -->
                                            <!-- <form action="#"
                                                method="POST"
                                                onsubmit="return confirm('Delete this customer?')">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    class="w-9 h-9 rounded-lg bg-red-100 text-red-600
                                                    hover:bg-red-200 flex items-center justify-center">

                                                    <i class="ti ti-trash"></i>

                                                </button>

                                            </form> -->

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5" class="py-16 text-center">

                                        <div class="flex flex-col items-center">

                                            <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center mb-4">

                                                <i class="ti ti-users text-4xl text-gray-400"></i>

                                            </div>

                                            <h3 class="text-lg font-semibold">
                                                No Customers Found
                                            </h3>

                                            <p class="text-gray-500 mt-1">
                                                Start by creating your first customer.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $customers->links() }}
            </div>

        </main>

    </div>

</div>

@endsection