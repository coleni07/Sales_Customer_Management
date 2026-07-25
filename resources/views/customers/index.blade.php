@extends('layouts.app')
@php $pageTitle = 'Customers'; @endphp

@section('content')

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6" x-data="{ modalOpen: false, selected: null }">

        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <div class="flex items-center gap-3 flex-1 min-w-[260px]">
                <div class="relative flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Ticket ID, Customer"
                           class="w-full bg-gray-100 rounded-full pl-11 pr-4 py-2.5 text-sm text-gray-600 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>

                <button type="submit" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 transition rounded-full px-5 py-2.5 text-sm font-medium text-gray-700">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>
            </div>

            <a href="{{ route('purchase-history.index') }}" class="bg-navy hover:bg-navyDark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                Purchase History
            </a>
        </form>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-800">
                        <th class="text-left font-bold px-5 py-4">Customer ID</th>
                        <th class="text-left font-bold px-5 py-4">Name</th>
                        <th class="text-left font-bold px-5 py-4">Location</th>
                        <th class="text-left font-bold px-5 py-4">Phone</th>
                        <th class="text-center font-bold px-5 py-4">Total Orders</th>
                        <th class="text-center font-bold px-5 py-4">Status</th>
                        <th class="text-center font-bold px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        @php
                            $lastOrder = $customer->orders->first();
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 font-semibold text-gray-800">{{ $customer->customer_code }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->name }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->location }}</td>
                            <td class="px-5 py-4 text-gray-700 font-mono">{{ $customer->masked_phone }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-800">{{ $customer->total_orders }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($customer->status === 'Active')
                                    <span class="inline-block bg-green-200 text-green-800 text-xs font-semibold px-4 py-1.5 rounded-full">Active</span>
                                @else
                                    <span class="inline-block bg-red-200 text-red-700 text-xs font-semibold px-4 py-1.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-4">
                                    <button type="button"
                                            title="View {{ $customer->name }}'s full information"
                                            class="text-gray-600 hover:text-navy transition text-base"
                                            @click="selected = {
                                                name: @js($customer->name),
                                                customerCode: @js($customer->customer_code),
                                                email: @js($customer->email),
                                                phone: @js($customer->phone ?: 'N/A'),
                                                address: @js($customer->address ?: 'N/A'),
                                                location: @js($customer->location ?: 'N/A'),
                                                totalOrders: @js($customer->total_orders),
                                                ordersCount: @js($customer->orders_count),
                                                status: @js($customer->status),
                                                memberSince: @js(optional($customer->created_at)->format('jS F Y')),
                                                lastOrderDate: @js(optional($lastOrder?->payment_date)->format('jS F Y')),
                                                lastOrderNumber: @js($lastOrder?->order_number),
                                                historyUrl: @js(route('purchase-history.index', ['customer_id' => $customer->id])),
                                            }; modalOpen = true">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>

                                    <a href="{{ route('purchase-history.index', ['customer_id' => $customer->id]) }}"
                                       title="View {{ $customer->name }}'s complete purchase history"
                                       class="text-gray-600 hover:text-navy transition text-base">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-400">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between mt-5 flex-wrap gap-3">
            <p class="text-sm text-gray-400">
                Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
            </p>
            <div class="flex items-center gap-1 [&_p]:hidden [&_a]:w-8 [&_a]:h-8 [&_span]:w-8 [&_span]:h-8 [&_a]:flex [&_span]:flex [&_a]:items-center [&_span]:items-center [&_a]:justify-center [&_span]:justify-center [&_a]:rounded-lg [&_span]:rounded-lg [&_a]:border [&_a]:border-gray-200 [&_a]:text-sm [&_a]:text-gray-600 [&_a]:hover:bg-gray-50">
                {{ $customers->onEachSide(1)->links() }}
            </div>
        </div>

        {{-- Customer full information modal --}}
        <div x-show="modalOpen"
             x-cloak
             x-transition.opacity
             class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4"
             @click.self="modalOpen = false"
             @keydown.escape.window="modalOpen = false">

            <div x-show="modalOpen" x-transition
                 class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

                <template x-if="selected">
                    <div>
                        <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900" x-text="selected.name"></h2>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="selected.customerCode"></p>
                            </div>
                            <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-700 transition">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</p>
                                    <p class="text-sm text-gray-800 mt-1 break-words" x-text="selected.email"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone</p>
                                    <p class="text-sm text-gray-800 mt-1" x-text="selected.phone"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</p>
                                    <p class="text-sm text-gray-800 mt-1" x-text="selected.location"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                                    <p class="text-sm text-gray-800 mt-1" x-text="selected.status"></p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Address</p>
                                    <p class="text-sm text-gray-800 mt-1" x-text="selected.address"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Member Since</p>
                                    <p class="text-sm text-gray-800 mt-1" x-text="selected.memberSince || 'N/A'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Orders</p>
                                    <p class="text-sm text-gray-800 mt-1" x-text="selected.totalOrders"></p>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Most Recent Order</p>
                                <template x-if="selected.lastOrderNumber">
                                    <p class="text-sm text-gray-800">
                                        Order <span class="font-semibold" x-text="selected.lastOrderNumber"></span>
                                        &middot; <span x-text="selected.lastOrderDate"></span>
                                    </p>
                                </template>
                                <template x-if="!selected.lastOrderNumber">
                                    <p class="text-sm text-gray-400">No orders yet.</p>
                                </template>
                            </div>
                        </div>

                        <div class="px-6 pb-6 flex items-center justify-end gap-3">
                            <button type="button" @click="modalOpen = false"
                                    class="px-5 py-2.5 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                                Close
                            </button>
                            <a :href="selected.historyUrl"
                               class="bg-navy hover:bg-navyDark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                                View Full Purchase History
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

@endsection