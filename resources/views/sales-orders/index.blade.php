@extends('layouts.app')

@php $pageTitle = 'Sales Order'; @endphp

@section('content')
    <div x-data="salesOrderPanel({{ $selectedOrder?->toJson() ?? 'null' }}, {{ Js::from($ordersData) }})" x-init="init()" class="space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">

            <div class="lg:col-span-2 space-y-4">

                <!-- Order Status Tracking -->
                <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
                    <h2 class="font-semibold text-slate-800 mb-4">Order Status Tracking</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        @php
                            $trackMeta = [
                                'pending' => ['label' => 'Pending', 'sub' => 'Draft', 'bar' => 'bg-amber-400', 'chip' => 'bg-amber-100 text-amber-600'],
                                'processing' => ['label' => 'Processing', 'sub' => 'Approved', 'bar' => 'bg-blue-500', 'chip' => 'bg-blue-100 text-blue-600'],
                                'shipped' => ['label' => 'Shipped', 'sub' => 'In Transit', 'bar' => 'bg-violet-500', 'chip' => 'bg-violet-100 text-violet-600'],
                                'delivered' => ['label' => 'Delivered', 'sub' => 'Completed', 'bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-100 text-emerald-600'],
                                'cancelled' => ['label' => 'Cancelled', 'sub' => 'Void', 'bar' => 'bg-rose-500', 'chip' => 'bg-rose-100 text-rose-600'],
                            ];
                            $maxCount = max($statusSummary->max('count'), 1);
                        @endphp
                        @foreach ($statusSummary as $row)
                            @php $meta = $trackMeta[$row['status']]; @endphp
                            <div class="rounded-lg border border-slate-100 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $meta['chip'] }}">{{ $meta['label'] }}</span>
                                </div>
                                <p class="text-xs text-slate-400">{{ $meta['sub'] }}</p>
                                <p class="text-sm font-semibold text-slate-700 mt-1">{{ $row['count'] }} orders</p>
                                <p class="text-xs text-slate-400">₱{{ number_format($row['total'], 2) }}</p>
                                <div class="mt-2 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $meta['bar'] }} rounded-full transition-all duration-700 ease-out"
                                        style="width: {{ round(($row['count'] / $maxCount) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Sales Order Listing -->
                <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
                    <div class="flex items-center justify-between mb-3 gap-4 flex-wrap">
                        <h2 class="font-semibold text-slate-800">Sales Order Listing</h2>

                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" x-model="search" @input="page = 1"
                                    placeholder="Search Order ID, Customer"
                                    class="w-64 bg-slate-100 rounded-full pl-10 pr-4 py-2 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand/30">
                            </div>

                            <div class="relative" @click.outside="showFilter = false">
                                <button type="button" @click="showFilter = !showFilter"
                                    class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 transition rounded-full px-4 py-2 text-sm font-medium text-slate-700">
                                    <i class="fa-solid fa-filter text-xs"></i>
                                    <span x-text="sortLabel()"></span>
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </button>

                                <div x-show="showFilter" x-cloak x-transition
                                    class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-2 z-10 text-sm">
                                    <button type="button" @click="sortBy = 'az'; showFilter = false; page = 1"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2"
                                        :class="sortBy === 'az' ? 'text-brand font-semibold' : 'text-slate-700'">
                                        <i class="fa-solid fa-arrow-down-a-z w-4"></i> A - Z
                                    </button>
                                    <button type="button" @click="sortBy = 'za'; showFilter = false; page = 1"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2"
                                        :class="sortBy === 'za' ? 'text-brand font-semibold' : 'text-slate-700'">
                                        <i class="fa-solid fa-arrow-down-z-a w-4"></i> Z - A
                                    </button>
                                    <button type="button" @click="sortBy = 'date_new'; showFilter = false; page = 1"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2"
                                        :class="sortBy === 'date_new' ? 'text-brand font-semibold' : 'text-slate-700'">
                                        <i class="fa-solid fa-calendar w-4"></i> Date: Newest first
                                    </button>
                                    <button type="button" @click="sortBy = 'date_old'; showFilter = false; page = 1"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 flex items-center gap-2"
                                        :class="sortBy === 'date_old' ? 'text-brand font-semibold' : 'text-slate-700'">
                                        <i class="fa-solid fa-calendar w-4"></i> Date: Oldest first
                                    </button>
                                    <hr class="my-2 border-slate-100">
                                    <button type="button" @click="sortBy = 'default'; showFilter = false; page = 1"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-500">
                                        <i class="fa-solid fa-rotate-left w-4"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-400 border-b border-slate-100">
                                    <th class="py-2 font-medium">Order ID</th>
                                    <th class="py-2 font-medium">Customer</th>
                                    <th class="py-2 font-medium">Amount</th>
                                    <th class="py-2 font-medium">Status</th>
                                    <th class="py-2 font-medium">Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="order in paginatedOrders" :key="order.id">
                                    <tr :id="'order-row-' + order.id" @click="loadOrder(order.id)"
                                        :class="selected && selected.id === order.id ? 'bg-brand/5' : ''"
                                        class="border-b border-slate-50 hover:bg-slate-50 cursor-pointer transition-colors">
                                        <td class="py-2.5 font-medium text-brand-dark" x-text="order.order_no"></td>
                                        <td class="py-2.5" x-text="order.customer_name"></td>
                                        <td class="py-2.5" x-text="'₱' + order.amount_label"></td>
                                        <td class="py-2.5">
                                            <span class="badge-in text-xs px-2.5 py-1 rounded-full font-medium"
                                                :class="order.status_classes" x-text="order.status_label"></span>
                                        </td>
                                        <td class="py-2.5" x-text="order.payment_label"></td>
                                    </tr>
                                </template>

                                <tr x-show="orders.length > 0 && filteredOrders.length === 0">
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        No orders match "<span x-text="search"></span>".
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-sm text-slate-500">
                        <span class="whitespace-nowrap">
                            Showing <span x-text="filteredOrders.length === 0 ? 0 : ((page - 1) * perPage) + 1"></span> to <span
                                x-text="Math.min(page * perPage, filteredOrders.length)"></span>
                            of <span x-text="filteredOrders.length"></span> results
                        </span>

                        <div class="flex items-center gap-1" x-show="totalPages > 1">
                            <button type="button" @click="goToPage(page - 1)" :disabled="page === 1"
                                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>

                            <template x-for="(n, idx) in pageNumbers" :key="idx">
                                <span>
                                    <button type="button" x-show="n !== '...'" @click="goToPage(n)"
                                        class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium"
                                        :class="page === n ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-100'"
                                        x-text="n"></button>
                                    <span x-show="n === '...'"
                                        class="w-8 h-8 flex items-center justify-center text-slate-400 select-none">&hellip;</span>
                                </span>
                            </template>

                            <button type="button" @click="goToPage(page + 1)" :disabled="page === totalPages"
                                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>

                        <span class="whitespace-nowrap text-slate-400">Records: <span x-text="filteredOrders.length"></span></span>
                    </div>

                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100 text-sm">
                        <span class="text-slate-500">On Approval Status:</span>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-600 font-medium">Approved -
                            {{ $approvedCount }}</span>
                        <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-600 font-medium">Unapproved -
                            {{ $unapprovedCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Order detail panel -->
            <div class="bg-white rounded-xl p-5 shadow-sm card-hover flex flex-col" x-cloak x-show="selected">
                <template x-if="selected">
                    <div class="flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs text-slate-400">Order</p>
                                <h3 class="font-semibold text-slate-800" x-text="'#' + selected.order_no"></h3>
                                <p class="text-xs text-slate-400 mt-1" x-text="'Customer: ' + selected.customer"></p>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded-full font-medium text-white"
                                :class="statusClass(selected.status)" x-text="selected.status_label"></span>
                        </div>

                        <!-- Edit form: this is what writes changes back to MySQL -->
                        <div class="mb-4 p-3 rounded-lg bg-slate-50 border border-slate-100 space-y-2">
                            <p class="text-xs font-semibold text-slate-400 uppercase">Update Order</p>
                            <div class="flex gap-2">
                                <select x-model="selected.status"
                                    class="flex-1 text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-white">
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <select x-model="selected.approval_status"
                                    class="flex-1 text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-white">
                                    <option value="approved">Approved</option>
                                    <option value="unapproved">Unapproved</option>
                                </select>
                            </div>
                            <button @click="saveOrder()" :disabled="saving"
                                class="w-full text-sm font-medium bg-brand text-white rounded-lg py-1.5 hover:bg-brand-dark transition-colors disabled:opacity-50">
                                <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                            </button>
                            <p x-show="savedMessage" x-transition class="text-xs text-emerald-600 text-center"
                                x-text="savedMessage"></p>
                        </div>

                        <div class="mb-3">
                            <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Items</p>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-400">
                                        <th class="pb-1 font-medium">Item</th>
                                        <th class="pb-1 font-medium">Qty</th>
                                        <th class="pb-1 font-medium text-right">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in selected.items" :key="item.id">
                                        <tr>
                                            <td class="py-1" x-text="item.name"></td>
                                            <td class="py-1" x-text="item.qty"></td>
                                            <td class="py-1 text-right" x-text="'₱' + item.price"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="space-y-1.5 text-sm border-t border-slate-100 pt-3">
                            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span
                                    x-text="'₱' + selected.subtotal"></span></div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Discounts <span
                                        class="text-xs bg-slate-100 px-1.5 py-0.5 rounded"
                                        x-text="selected.discount_label"></span></span>
                                <span class="text-rose-500" x-text="'-₱' + selected.discount_amount"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tax <span class="text-xs bg-slate-100 px-1.5 py-0.5 rounded"
                                        x-text="selected.tax_label"></span></span>
                                <span x-text="'+₱' + selected.tax_amount"></span>
                            </div>
                            <div class="flex justify-between"><span class="text-slate-500">Shipping</span><span
                                    x-text="'₱' + selected.shipping_fee"></span></div>
                            <div class="flex justify-between font-semibold text-slate-800 pt-2 border-t border-slate-100">
                                <span>Total</span><span x-text="'₱' + selected.amount"></span>
                            </div>
                        </div>

                        <a href="#"
                            class="mt-auto flex items-center justify-between px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors text-sm">
                            <span class="flex items-center gap-2">📦 Inventory</span>
                            <span class="text-slate-400"
                                x-text="'Stock Allocated (' + selected.warehouse_code + ')'"></span>
                        </a>
                        <a href="#"
                            class="mt-2 flex items-center justify-between px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors text-sm">
                            <span class="flex items-center gap-2">💰 Finance</span>
                            <span class="text-slate-400" x-text="'Pending Receivable (' + selected.gl_code + ')'"></span>
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function salesOrderPanel(initial, orders) {
            return {
                selected: initial,
                saving: false,
                savedMessage: '',
                orders: orders,
                search: '',
                sortBy: 'default',
                showFilter: false,
                page: 1,
                perPage: 7,
                get filteredOrders() {
                    let list = this.orders;

                    const q = this.search.trim().toLowerCase();
                    if (q !== '') {
                        list = list.filter(o =>
                            o.order_no.toLowerCase().includes(q) ||
                            o.customer_name.toLowerCase().includes(q)
                        );
                    }

                    list = [...list];
                    if (this.sortBy === 'az') {
                        list.sort((a, b) => a.customer_name.localeCompare(b.customer_name));
                    } else if (this.sortBy === 'za') {
                        list.sort((a, b) => b.customer_name.localeCompare(a.customer_name));
                    } else if (this.sortBy === 'date_new') {
                        list.sort((a, b) => new Date(b.order_date) - new Date(a.order_date));
                    } else if (this.sortBy === 'date_old') {
                        list.sort((a, b) => new Date(a.order_date) - new Date(b.order_date));
                    }

                    return list;
                },
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredOrders.length / this.perPage));
                },
                get paginatedOrders() {
                    if (this.page > this.totalPages) this.page = this.totalPages;
                    const start = (this.page - 1) * this.perPage;
                    return this.filteredOrders.slice(start, start + this.perPage);
                },
                get pageNumbers() {
                    const total = this.totalPages;
                    if (total <= 12) {
                        return Array.from({ length: total }, (_, i) => i + 1);
                    }

                    const current = this.page;
                    let start = Math.max(1, current - 4);
                    let end = start + 9;
                    if (end > total - 2) {
                        end = total - 2;
                        start = Math.max(1, end - 9);
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
                    }[this.sortBy];
                },
                init() {
                    // Deep-link support: /sales-orders?highlight=123 auto-opens
                    // that order's detail panel, so notification links can jump
                    // straight to the relevant record instead of just the page.
                    const params = new URLSearchParams(window.location.search);
                    const highlightId = params.get('highlight');
                    if (highlightId) {
                        this.loadOrder(highlightId);

                        // Jump pagination to whichever page the highlighted
                        // order actually falls on within the current sort/filter.
                        const index = this.filteredOrders.findIndex(o => o.id === parseInt(highlightId, 10));
                        if (index !== -1) {
                            this.page = Math.floor(index / this.perPage) + 1;
                        }

                        this.$nextTick(() => {
                            const row = document.getElementById('order-row-' + highlightId);
                            if (row) {
                                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                row.classList.add('bg-amber-50');
                                setTimeout(() => row.classList.remove('bg-amber-50'), 2000);
                            }
                        });
                    }
                },
                loadOrder(id) {
                    fetch(`/sales-orders/${id}`)
                        .then(res => res.json())
                        .then(data => { this.selected = { id: parseInt(id, 10), ...data }; this.savedMessage = ''; });
                },
                saveOrder() {
                    this.saving = true;
                    this.savedMessage = '';
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(`/sales-orders/${this.selected.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            status: this.selected.status,
                            approval_status: this.selected.approval_status,
                        }),
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.saving = false;
                            this.savedMessage = 'Saved to database ✓';
                            // Reload so the table row, tracking cards, and approval
                            // counts all reflect the change that was just saved.
                            setTimeout(() => location.reload(), 700);
                        })
                        .catch(() => {
                            this.saving = false;
                            this.savedMessage = 'Something went wrong — please try again.';
                        });
                },
                statusClass(status) {
                    return {
                        pending: 'bg-amber-400',
                        processing: 'bg-blue-500',
                        shipped: 'bg-violet-500',
                        delivered: 'bg-emerald-500',
                        cancelled: 'bg-rose-500',
                    }[status] ?? 'bg-slate-400';
                }
            }
        }
    </script>
@endsection