@extends('layouts.app')
@php $pageTitle = 'Customers'; @endphp

@section('content')

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6"
         x-data='customerTable({{ json_encode($customersData, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}, {{ json_encode($initialSearch, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}, {{ json_encode($initialSort, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }})'>

        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <div class="flex items-center gap-3 flex-1 min-w-[260px]">
                <div class="relative flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           x-model.debounce.300ms="search"
                           @input="page = 1"
                           placeholder="Search Customer ID, Name"
                           autocomplete="off"
                           class="w-full bg-gray-100 rounded-full pl-11 pr-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand/40 focus:bg-white border border-transparent focus:border-brand/30 transition">
                </div>

                <div class="relative" @click.outside="showFilter = false">
                    <button type="button" @click="showFilter = !showFilter"
                            class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 transition rounded-full px-5 py-2.5 text-sm font-medium text-gray-700">
                        <i class="fa-solid fa-filter"></i>
                        <span x-text="sortLabel()"></span>
                        <i class="fa-solid fa-chevron-down text-xs transition" :class="showFilter ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="showFilter" x-cloak x-transition
                         class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-20">
                        <p class="px-4 pt-1 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Sort by</p>
                        <button type="button" @click="sortBy = 'az'; showFilter = false; page = 1"
                                class="flex items-center gap-3 px-4 py-2 text-sm transition w-full text-left"
                                :class="sortBy === 'az' ? 'text-navy font-semibold bg-gray-50' : 'text-gray-600 hover:bg-gray-50'">
                            <i class="fa-solid fa-arrow-down-a-z w-4 text-gray-400"></i>
                            Name (A - Z)
                        </button>
                        <button type="button" @click="sortBy = 'za'; showFilter = false; page = 1"
                                class="flex items-center gap-3 px-4 py-2 text-sm transition w-full text-left"
                                :class="sortBy === 'za' ? 'text-navy font-semibold bg-gray-50' : 'text-gray-600 hover:bg-gray-50'">
                            <i class="fa-solid fa-arrow-down-z-a w-4 text-gray-400"></i>
                            Name (Z - A)
                        </button>
                        <button type="button" @click="sortBy = 'date_new'; showFilter = false; page = 1"
                                class="flex items-center gap-3 px-4 py-2 text-sm transition w-full text-left"
                                :class="sortBy === 'date_new' ? 'text-navy font-semibold bg-gray-50' : 'text-gray-600 hover:bg-gray-50'">
                            <i class="fa-solid fa-calendar-days w-4 text-gray-400"></i>
                            Newest First
                        </button>
                        <button type="button" @click="sortBy = 'date_old'; showFilter = false; page = 1"
                                class="flex items-center gap-3 px-4 py-2 text-sm transition w-full text-left"
                                :class="sortBy === 'date_old' ? 'text-navy font-semibold bg-gray-50' : 'text-gray-600 hover:bg-gray-50'">
                            <i class="fa-solid fa-calendar-days w-4 text-gray-400"></i>
                            Oldest First
                        </button>
                        <hr class="my-2 border-gray-100">
                        <button type="button" @click="sortBy = 'default'; showFilter = false; page = 1"
                                class="flex items-center gap-3 px-4 py-2 text-sm transition w-full text-left text-gray-500 hover:bg-gray-50">
                            <i class="fa-solid fa-rotate-left w-4 text-gray-400"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" @click="viewAllHistory(@js(route('purchase-history.index')))"
                    class="bg-navy hover:bg-navyDark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm shadow-sm shadow-navy/20">
                <i class="fa-solid fa-receipt mr-1"></i>
                Purchase History
            </button>
        </div>

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
                    <template x-for="customer in paginatedCustomers" :key="customer.id">
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-5 py-4 font-semibold text-gray-800" x-text="customer.customer_code"></td>
                            <td class="px-5 py-4 text-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0" :class="customer.avatarColor">
                                        <span x-text="customer.initials"></span>
                                    </span>
                                    <span class="font-medium" x-text="customer.name"></span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600" x-text="customer.location"></td>
                            <td class="px-5 py-4 text-gray-600 font-mono tracking-wide" x-text="customer.masked_phone"></td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-800" x-text="customer.total_orders"></td>
                            <td class="px-5 py-4 text-center">
                                <template x-if="customer.status === 'Active'">
                                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-semibold px-4 py-1.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Active
                                    </span>
                                </template>
                                <template x-if="customer.status !== 'Active'">
                                    <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-600 text-xs font-semibold px-4 py-1.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Inactive
                                    </span>
                                </template>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button type="button"
                                        title="View customer profile"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-navy hover:bg-navy/5 transition text-base"
                                        @click="openCustomer(customer)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="customers.length > 0 && filteredCustomers.length === 0">
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <i class="fa-solid fa-user-slash text-2xl mb-2 block"></i>
                            No customers found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between mt-5 flex-wrap gap-3">
            <p class="text-sm text-gray-400">
                Showing <span class="font-medium text-gray-600" x-text="filteredCustomers.length === 0 ? 0 : ((page - 1) * perPage) + 1"></span> to
                <span class="font-medium text-gray-600" x-text="Math.min(page * perPage, filteredCustomers.length)"></span> of
                <span class="font-medium text-gray-600" x-text="filteredCustomers.length"></span> results
            </p>

            <div class="flex items-center flex-wrap justify-center gap-1" x-show="totalPages > 1">
                <button type="button" @click="goToPage(page - 1)" :disabled="page === 1"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed text-gray-600">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>

                <template x-for="(n, idx) in pageNumbers" :key="idx">
                    <span>
                        <button type="button" x-show="n !== '...'" @click="goToPage(n)"
                                class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium"
                                :class="page === n ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                                x-text="n"></button>
                        <span x-show="n === '...'"
                              class="w-8 h-8 flex items-center justify-center text-gray-400 select-none">&hellip;</span>
                    </span>
                </template>

                <button type="button" @click="goToPage(page + 1)" :disabled="page === totalPages"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed text-gray-600">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
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

                <div class="flex items-center gap-6 px-6 pt-4 border-b border-gray-100 overflow-x-auto">
                    <template x-for="tab in historyTabs" :key="tab.key">
                        <button type="button" @click="loadHistory(tab.key)"
                                class="text-sm pb-3 -mb-px whitespace-nowrap"
                                :class="historyStatus === tab.key ? 'font-bold text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700'"
                                x-text="tab.label"></button>
                    </template>
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

    <script>
        function customerTable(customers, initialSearch = '', initialSort = 'default') {
            return {
                customers,
                selected: null,
                modalOpen: false,
                showFilter: false,
                search: initialSearch,
                sortBy: initialSort,
                page: 1,
                perPage: 7,
                historyOpen: false,
                historyLoading: false,
                historyData: null,
                historyScope: 'customer',
                historyBaseUrl: '',
                historyStatus: 'all',
                historyTabs: [
                    { key: 'all', label: 'All' },
                    { key: 'pending', label: 'Pending' },
                    { key: 'shipped', label: 'Shipped' },
                    { key: 'delivered', label: 'Delivered' },
                    { key: 'cancelled', label: 'Cancelled' },
                ],
                get filteredCustomers() {
                    let list = [...this.customers];
                    const q = this.search.trim().toLowerCase();

                    if (q !== '') {
                        list = list.filter(customer =>
                            customer.customer_code.toLowerCase().includes(q) ||
                            customer.name.toLowerCase().includes(q)
                        );
                    }

                    if (this.sortBy === 'az') {
                        list.sort((a, b) => a.name.localeCompare(b.name));
                    } else if (this.sortBy === 'za') {
                        list.sort((a, b) => b.name.localeCompare(a.name));
                    } else if (this.sortBy === 'date_new') {
                        list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
                    } else if (this.sortBy === 'date_old') {
                        list.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
                    }

                    return list;
                },
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredCustomers.length / this.perPage));
                },
                get paginatedCustomers() {
                    if (this.page > this.totalPages) this.page = this.totalPages;
                    const start = (this.page - 1) * this.perPage;
                    return this.filteredCustomers.slice(start, start + this.perPage);
                },
                get pageNumbers() {
                    const total = this.totalPages;
                    if (total <= 7) {
                        return Array.from({ length: total }, (_, i) => i + 1);
                    }

                    const current = this.page;
                    let start = Math.max(1, current - 2);
                    let end = start + 4;
                    if (end > total - 2) {
                        end = total - 2;
                        start = Math.max(1, end - 4);
                    }

                    const pages = [];
                    for (let i = start; i <= end; i++) pages.push(i);
                    if (end < total - 1) pages.push('...');
                    for (let i = Math.max(end + 1, total - 1); i <= total; i++) {
                        if (!pages.includes(i)) pages.push(i);
                    }

                    return pages;
                },
                goToPage(n) {
                    if (n < 1 || n > this.totalPages) return;
                    this.page = n;
                },
                sortLabel() {
                    return {
                        default: 'Filter',
                        az: 'Name: A-Z',
                        za: 'Name: Z-A',
                        date_new: 'Date: Newest',
                        date_old: 'Date: Oldest',
                    }[this.sortBy] || 'Filter';
                },
                openCustomer(customer) {
                    this.selected = customer;
                    this.modalOpen = true;
                },
                async loadHistory(status) {
                    this.historyStatus = status;
                    this.historyLoading = true;
                    const url = this.historyBaseUrl + (this.historyBaseUrl.includes('?') ? '&' : '?') + 'status=' + status;
                    try {
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        this.historyData = await res.json();
                    } catch (e) {
                        this.historyData = { error: true };
                    } finally {
                        this.historyLoading = false;
                    }
                },
                viewHistory() {
                    this.historyScope = 'customer';
                    this.historyBaseUrl = this.selected.historyUrl;
                    this.modalOpen = false;
                    this.historyOpen = true;
                    this.loadHistory('all');
                },
                viewAllHistory(url) {
                    this.historyScope = 'all';
                    this.historyBaseUrl = url;
                    this.selected = null;
                    this.historyOpen = true;
                    this.loadHistory('all');
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
            };
        }
    </script>

@endsection
