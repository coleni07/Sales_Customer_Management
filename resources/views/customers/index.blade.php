@extends('layouts.app')
@php $pageTitle = 'Customers'; @endphp

@section('content')

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6"
         x-data="{
            modalOpen: false,
            selected: null,
            filterOpen: false,
            searchTimeout: null,
            liveSearch(el) {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => el.form.requestSubmit(), 400);
            },
            historyOpen: false,
            historyLoading: false,
            historyData: null,
            historyScope: 'customer',
            async viewHistory() {
                this.historyScope = 'customer';
                this.historyLoading = true;
                this.historyData = null;
                this.modalOpen = false;
                this.historyOpen = true;
                try {
                    const res = await fetch(this.selected.historyUrl, {
                        headers: { 'Accept': 'application/json' }
                    });
                    this.historyData = await res.json();
                } catch (e) {
                    this.historyData = { error: true };
                } finally {
                    this.historyLoading = false;
                }
            },
            async viewAllHistory(url) {
                this.historyScope = 'all';
                this.selected = null;
                this.historyLoading = true;
                this.historyData = null;
                this.historyOpen = true;
                try {
                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json' }
                    });
                    this.historyData = await res.json();
                } catch (e) {
                    this.historyData = { error: true };
                } finally {
                    this.historyLoading = false;
                }
            },
            statusBadge(status) {
                const map = {
                    delivered: 'bg-green-100 text-green-700',
                    shipped: 'bg-sky-100 text-sky-700',
                    processing: 'bg-amber-100 text-amber-700',
                    pending: 'bg-gray-100 text-gray-600',
                    cancelled: 'bg-red-100 text-red-600',
                };
                return map[status] || 'bg-gray-100 text-gray-600';
            }
         }">

        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <div class="flex items-center gap-3 flex-1 min-w-[260px]">
                <div class="relative flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Customer ID, Name"
                           autocomplete="off"
                           @input="liveSearch($el)"
                           class="w-full bg-gray-100 rounded-full pl-11 pr-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand/40 focus:bg-white border border-transparent focus:border-brand/30 transition">
                </div>

                {{-- Sort / Filter dropdown --}}
                <div class="relative" @click.outside="filterOpen = false">
                    <button type="button" @click="filterOpen = !filterOpen"
                            class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 transition rounded-full px-5 py-2.5 text-sm font-medium text-gray-700">
                        <i class="fa-solid fa-filter"></i>
                        Filter
                        <i class="fa-solid fa-chevron-down text-xs transition" :class="filterOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                         class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-20">
                        <p class="px-4 pt-1 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Sort by</p>
                        @php
                            $sortOptions = [
                                'name_asc' => ['label' => 'Name (A - Z)', 'icon' => 'fa-arrow-down-a-z'],
                                'name_desc' => ['label' => 'Name (Z - A)', 'icon' => 'fa-arrow-down-z-a'],
                                'date_new' => ['label' => 'Newest First', 'icon' => 'fa-calendar-days'],
                                'date_old' => ['label' => 'Oldest First', 'icon' => 'fa-calendar-days'],
                            ];
                        @endphp
                        @foreach ($sortOptions as $key => $option)
                            <a href="{{ route('customers.index', array_filter(['search' => request('search'), 'sort' => $key])) }}"
                               class="flex items-center gap-3 px-4 py-2 text-sm transition {{ $sort === $key ? 'text-navy font-semibold bg-gray-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                <i class="fa-solid {{ $option['icon'] }} w-4 text-gray-400"></i>
                                {{ $option['label'] }}
                                @if ($sort === $key)
                                    <i class="fa-solid fa-check ml-auto text-brand"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="button" @click="viewAllHistory(@js(route('purchase-history.index')))"
                    class="bg-navy hover:bg-navyDark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm shadow-sm shadow-navy/20">
                <i class="fa-solid fa-receipt mr-1"></i>
                Purchase History
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 border-b border-gray-200">
                        <th class="text-left font-semibold uppercase text-xs tracking-wide px-5 py-4">Customer ID</th>
                        <th class="text-left font-semibold uppercase text-xs tracking-wide px-5 py-4">Name</th>
                        <th class="text-left font-semibold uppercase text-xs tracking-wide px-5 py-4">Location</th>
                        <th class="text-left font-semibold uppercase text-xs tracking-wide px-5 py-4">Phone</th>
                        <th class="text-center font-semibold uppercase text-xs tracking-wide px-5 py-4">Total Orders</th>
                        <th class="text-center font-semibold uppercase text-xs tracking-wide px-5 py-4">Status</th>
                        <th class="text-center font-semibold uppercase text-xs tracking-wide px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        @php
                            $lastOrder = $customer->orders->first();
                            $initials = collect(explode(' ', $customer->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('');
                            $avatarPalette = ['bg-brand/15 text-brand-dark', 'bg-navy/10 text-navy', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-sky-100 text-sky-700'];
                            $avatarColor = $avatarPalette[$customer->id % count($avatarPalette)];
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-5 py-4 font-semibold text-gray-800">{{ $customer->customer_code }}</td>
                            <td class="px-5 py-4 text-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $avatarColor }}">
                                        {{ strtoupper($initials) }}
                                    </span>
                                    <span class="font-medium">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $customer->location }}</td>
                            <td class="px-5 py-4 text-gray-600 font-mono tracking-wide">{{ $customer->masked_phone }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-800">{{ $customer->total_orders }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($customer->status === 'Active')
                                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-semibold px-4 py-1.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-600 text-xs font-semibold px-4 py-1.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button type="button"
                                        title="View {{ $customer->name }}'s full information"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-navy hover:bg-navy/5 transition text-base"
                                        @click="selected = {
                                            name: @js($customer->name),
                                            customerCode: @js($customer->customer_code),
                                            email: @js($customer->email),
                                            phone: @js($customer->formatted_phone),
                                            address: @js($customer->address ?: 'N/A'),
                                            location: @js($customer->location ?: 'N/A'),
                                            totalOrders: @js($customer->total_orders),
                                            ordersCount: @js($customer->orders_count),
                                            status: @js($customer->status),
                                            memberSince: @js(optional($customer->created_at)->format('jS F Y')),
                                            lastOrderDate: @js(optional($lastOrder?->order_date)->format('jS F Y')),
                                            lastOrderNumber: @js($lastOrder?->order_no),
                                            historyUrl: @js(route('purchase-history.index', ['customer_id' => $customer->id])),
                                        }; modalOpen = true">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-user-slash text-2xl mb-2 block"></i>
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between mt-5 flex-wrap gap-3">
            <p class="text-sm text-gray-400">
                Showing <span class="font-medium text-gray-600">{{ $customers->firstItem() ?? 0 }}</span> to
                <span class="font-medium text-gray-600">{{ $customers->lastItem() ?? 0 }}</span> of
                <span class="font-medium text-gray-600">{{ $customers->total() }}</span> entries
            </p>

            @if ($customers->lastPage() > 1)
                <div class="flex items-center gap-1.5">
                    @if ($customers->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </span>
                    @else
                        <a href="{{ $customers->previousPageUrl() }}"
                           class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </a>
                    @endif

                    @foreach ($customers->getUrlRange(1, $customers->lastPage()) as $page => $url)
                        @if ($page === $customers->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-navy text-white text-sm font-semibold">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    @if ($customers->hasMorePages())
                        <a href="{{ $customers->nextPageUrl() }}"
                           class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </span>
                    @endif
                </div>
            @endif
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
                            <button type="button" @click="viewHistory()"
                               class="bg-navy hover:bg-navyDark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                                View Full Purchase History
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Purchase history popup (stays on this page, no navigation) --}}
        <div x-show="historyOpen"
             x-cloak
             x-transition.opacity
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             @click.self="historyOpen = false"
             @keydown.escape.window="historyOpen = false">

            <div x-show="historyOpen" x-transition
                 class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">

                <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-gray-100 sticky top-0 bg-white">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Purchase History</h2>
                        <p class="text-xs text-gray-500 mt-0.5"
                           x-text="historyScope === 'customer' && selected ? selected.name + ' &middot; ' + selected.customerCode : 'All Customers'"></p>
                    </div>
                    <button type="button" @click="historyOpen = false" class="text-gray-400 hover:text-gray-700 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="px-6 py-5">

                    <template x-if="historyLoading">
                        <p class="text-center text-gray-400 py-10">
                            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading purchase history...
                        </p>
                    </template>

                    <template x-if="!historyLoading && historyData && historyData.error">
                        <p class="text-center text-red-400 py-10">Couldn't load purchase history. Please try again.</p>
                    </template>

                    <template x-if="!historyLoading && historyData && !historyData.error">
                        <div>
                            <div class="grid grid-cols-3 gap-3 mb-5">
                                <div class="border border-gray-200 rounded-xl p-3 text-center">
                                    <p class="text-xl font-bold text-gray-900" x-text="historyData.summary.orders_count"></p>
                                    <p class="text-xs text-gray-500 mt-0.5">Orders</p>
                                </div>
                                <div class="border border-gray-200 rounded-xl p-3 text-center">
                                    <p class="text-xl font-bold text-gray-900" x-text="historyData.summary.items_count"></p>
                                    <p class="text-xs text-gray-500 mt-0.5">Items</p>
                                </div>
                                <div class="border border-gray-200 rounded-xl p-3 text-center">
                                    <p class="text-xl font-bold text-gray-900" x-text="'Php ' + Number(historyData.summary.total_spent).toLocaleString(undefined, {minimumFractionDigits: 2})"></p>
                                    <p class="text-xs text-gray-500 mt-0.5">Total Spent</p>
                                </div>
                            </div>

                            <template x-if="historyData.orders.length === 0">
                                <p class="text-center text-gray-400 py-10">No orders found for this customer.</p>
                            </template>

                            <template x-for="order in historyData.orders" :key="order.order_no">
                                <div class="border border-gray-200 rounded-xl p-4 mb-4">
                                    <div class="flex items-start justify-between mb-3 flex-wrap gap-2">
                                        <div>
                                            <template x-if="historyScope === 'all'">
                                                <p class="text-sm font-semibold text-gray-900" x-text="order.customer_name + ' · ' + order.customer_code"></p>
                                            </template>
                                            <p class="text-sm text-gray-800">Order : <span class="font-medium" x-text="order.order_no"></span></p>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="order.order_date"></p>
                                        </div>
                                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full"
                                              :class="statusBadge(order.status)"
                                              x-text="order.status.charAt(0).toUpperCase() + order.status.slice(1)"></span>
                                    </div>

                                    <div class="divide-y divide-gray-100">
                                        <template x-for="item in order.items" :key="item.product_name + order.order_no">
                                            <div class="flex items-center justify-between py-2.5">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900" x-text="item.product_name"></p>
                                                    <p class="text-xs text-gray-500" x-text="'Qty: ' + item.qty"></p>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-800" x-text="'Php ' + Number(item.price).toLocaleString(undefined, {minimumFractionDigits: 2})"></p>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-between pt-3 mt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500">&nbsp;</p>
                                        <p class="bg-gray-100 rounded-full px-4 py-1.5 text-sm font-semibold text-gray-800"
                                           x-text="'Total: Php ' + Number(order.amount).toLocaleString(undefined, {minimumFractionDigits: 2})"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="px-6 pb-6 flex items-center justify-between gap-3 sticky bottom-0 bg-white">
                    <template x-if="historyScope === 'customer'">
                        <button type="button" @click="historyOpen = false; modalOpen = true"
                                class="px-5 py-2.5 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Profile
                        </button>
                    </template>
                    <template x-if="historyScope !== 'customer'">
                        <span></span>
                    </template>
                    <button type="button" @click="historyOpen = false"
                            class="px-5 py-2.5 rounded-full text-sm font-medium text-white bg-navy hover:bg-navyDark transition">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
