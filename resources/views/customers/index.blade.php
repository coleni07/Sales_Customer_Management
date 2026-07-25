@extends('layouts.app')
@php $pageTitle = 'Customers'; @endphp

@section('content')

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6" x-data="{ showProfile: false, selected: null }">

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
                        <th class="text-center font-bold px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 font-semibold text-gray-800">{{ $customer->customer_code }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->name }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->location }}</td>
                            <td class="px-5 py-4 text-gray-700 font-mono text-[13px]">{{ $customer->phone_masked }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-800">{{ $customer->total_orders }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($customer->status === 'Active')
                                    <span class="inline-block bg-green-200 text-green-800 text-xs font-semibold px-4 py-1.5 rounded-full">Active</span>
                                @else
                                    <span class="inline-block bg-red-200 text-red-700 text-xs font-semibold px-4 py-1.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('purchase-history.index', ['customer_id' => $customer->id]) }}"
                                       title="View {{ $customer->name }}'s complete purchase history"
                                       class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-navy hover:text-white transition text-gray-700 text-xs font-semibold px-3.5 py-2 rounded-full">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        Purchase History
                                    </a>
                                    <button type="button"
                                            title="View {{ $customer->name }}'s full information"
                                            @click="selected = {
                                                code: @js($customer->customer_code),
                                                name: @js($customer->name),
                                                email: @js($customer->email),
                                                phone: @js($customer->phone ?? '—'),
                                                address: @js($customer->address ?? '—'),
                                                location: @js($customer->location ?? '—'),
                                                totalOrders: @js($customer->total_orders),
                                                status: @js($customer->status),
                                                since: @js(optional($customer->created_at)->format('jS F Y') ?? '—'),
                                            }; showProfile = true"
                                            class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-navy hover:text-white transition">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
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
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <div class="flex items-center gap-1 [&_p]:hidden [&_a]:w-8 [&_a]:h-8 [&_span]:w-8 [&_span]:h-8 [&_a]:flex [&_span]:flex [&_a]:items-center [&_span]:items-center [&_a]:justify-center [&_span]:justify-center [&_a]:rounded-lg [&_span]:rounded-lg [&_a]:border [&_a]:border-gray-200 [&_a]:text-sm [&_a]:text-gray-600 [&_a]:hover:bg-gray-50">
                {{ $customers->onEachSide(1)->links() }}
            </div></p>
        </div>

        <!-- Client profile modal -->
        <div x-show="showProfile" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-black/50" @click="showProfile = false" x-transition.opacity></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                 x-show="showProfile"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <template x-if="selected">
                    <div>
                        <div class="flex items-start justify-between mb-5">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide" x-text="selected.code"></p>
                                <h3 class="text-xl font-bold text-gray-900" x-text="selected.name"></h3>
                            </div>
                            <button type="button" @click="showProfile = false"
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="space-y-4 text-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-navy/10 text-navy flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-envelope text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Email</p>
                                    <p class="font-medium text-gray-800" x-text="selected.email"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-navy/10 text-navy flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Phone</p>
                                    <p class="font-medium text-gray-800 font-mono" x-text="selected.phone"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-navy/10 text-navy flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-location-dot text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Address</p>
                                    <p class="font-medium text-gray-800" x-text="selected.address"></p>
                                    <p class="text-xs text-gray-500" x-text="selected.location"></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-400">Total Orders</p>
                                    <p class="font-semibold text-gray-800" x-text="selected.totalOrders"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Status</p>
                                    <p class="font-semibold" :class="selected.status === 'Active' ? 'text-green-700' : 'text-red-600'" x-text="selected.status"></p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-400">Customer Since</p>
                                    <p class="font-semibold text-gray-800" x-text="selected.since"></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" @click="showProfile = false"
                                    class="bg-gray-100 hover:bg-gray-200 transition text-gray-700 font-semibold rounded-full px-5 py-2 text-sm">
                                Close
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

@endsection