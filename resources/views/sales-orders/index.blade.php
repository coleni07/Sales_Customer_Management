@extends('layouts.app')

@php $pageTitle = 'Sales Order'; @endphp

@section('content')
<div x-data="salesOrderPanel({{ $selectedOrder?->toJson() ?? 'null' }})" x-init="init()" class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
        
        <div class="lg:col-span-2 space-y-4">

            <!-- Order Status Tracking -->
            <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
                <h2 class="font-semibold text-slate-800 mb-4">Order Status Tracking</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @php
                        $trackMeta = [
                            'pending' => ['label' => 'Pending', 'sub' => 'Draft', 'bar' => 'bg-amber-400', 'chip' => 'bg-amber-100 text-amber-600'],
                            'processing' => ['label' => 'Processing', 'sub' => 'Approved', 'bar' => 'bg-blue-500', 'chip' => 'bg-blue-100 text-blue-600'],
                            'shipped' => ['label' => 'Shipped', 'sub' => 'In Transit', 'bar' => 'bg-violet-500', 'chip' => 'bg-violet-100 text-violet-600'],
                            'delivered' => ['label' => 'Delivered', 'sub' => 'Completed', 'bar' => 'bg-emerald-500', 'chip' => 'bg-emerald-100 text-emerald-600'],
                        ];
                        $maxCount = max($statusSummary->max('count'), 1);
                    @endphp
                    @foreach ($statusSummary as $row)
                        @php $meta = $trackMeta[$row['status']]; @endphp
                        <div class="rounded-lg border border-slate-100 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $meta['chip'] }}">{{ $meta['label'] }}</span>
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
                <h2 class="font-semibold text-slate-800 mb-3">Sales Order Listing</h2>
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
                            @foreach ($orders as $order)
                                <tr id="order-row-{{ $order->id }}" @click="loadOrder({{ $order->id }})"
                                    :class="selected && selected.id === {{ $order->id }} ? 'bg-brand/5' : ''"
                                    class="border-b border-slate-50 hover:bg-slate-50 cursor-pointer transition-colors">
                                    <td class="py-2.5 font-medium text-brand-dark">{{ $order->order_no }}</td>
                                    <td class="py-2.5">{{ $order->customer->name }}</td>
                                    <td class="py-2.5">₱{{ number_format($order->amount, 2) }}</td>
                                    <td class="py-2.5">
                                        <span class="badge-in text-xs px-2.5 py-1 rounded-full font-medium {{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    <td class="py-2.5">{{ $order->paymentLabel() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between mt-4 text-sm text-slate-500">
                    <div>{{ $orders->links() }}</div>
                    <div>Records: {{ $orders->total() }}</div>
                </div>

                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100 text-sm">
                    <span class="text-slate-500">On Approval Status:</span>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-600 font-medium">Approved - {{ $approvedCount }}</span>
                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-600 font-medium">Unapproved - {{ $unapprovedCount }}</span>
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
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium text-white" :class="statusClass(selected.status)" x-text="selected.status_label"></span>
                    </div>

                    <!-- Edit form: this is what writes changes back to MySQL -->
                    <div class="mb-4 p-3 rounded-lg bg-slate-50 border border-slate-100 space-y-2">
                        <p class="text-xs font-semibold text-slate-400 uppercase">Update Order</p>
                        <div class="flex gap-2">
                            <select x-model="selected.status" class="flex-1 text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-white">
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select x-model="selected.approval_status" class="flex-1 text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-white">
                                <option value="approved">Approved</option>
                                <option value="unapproved">Unapproved</option>
                            </select>
                        </div>
                        <button @click="saveOrder()" :disabled="saving"
                                class="w-full text-sm font-medium bg-brand text-white rounded-lg py-1.5 hover:bg-brand-dark transition-colors disabled:opacity-50">
                            <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                        </button>
                        <p x-show="savedMessage" x-transition class="text-xs text-emerald-600 text-center" x-text="savedMessage"></p>
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
                                <template x-for="item in selected.items" :key="item.name">
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
                        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span x-text="'₱' + selected.subtotal"></span></div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Discounts <span class="text-xs bg-slate-100 px-1.5 py-0.5 rounded" x-text="selected.discount_label"></span></span>
                            <span class="text-rose-500" x-text="'-₱' + selected.discount_amount"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tax <span class="text-xs bg-slate-100 px-1.5 py-0.5 rounded" x-text="selected.tax_label"></span></span>
                            <span x-text="'+₱' + selected.tax_amount"></span>
                        </div>
                        <div class="flex justify-between"><span class="text-slate-500">Shipping</span><span x-text="'₱' + selected.shipping_fee"></span></div>
                        <div class="flex justify-between font-semibold text-slate-800 pt-2 border-t border-slate-100">
                            <span>Total</span><span x-text="'₱' + selected.amount"></span>
                        </div>
                    </div>

                    <a href="#" class="mt-auto flex items-center justify-between px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors text-sm">
                        <span class="flex items-center gap-2">📦 Inventory</span>
                        <span class="text-slate-400" x-text="'Stock Allocated (' + selected.warehouse_code + ')'"></span>
                    </a>
                    <a href="#" class="mt-2 flex items-center justify-between px-3 py-2.5 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors text-sm">
                        <span class="flex items-center gap-2">💰 Finance</span>
                        <span class="text-slate-400" x-text="'Pending Receivable (' + selected.gl_code + ')'"></span>
                    </a>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function salesOrderPanel(initial) {
    return {
        selected: initial,
        saving: false,
        savedMessage: '',
        init() {
            // Deep-link support: /sales-orders?highlight=123 auto-opens
            // that order's detail panel, so notification links can jump
            // straight to the relevant record instead of just the page.
            const params = new URLSearchParams(window.location.search);
            const highlightId = params.get('highlight');
            if (highlightId) {
                this.loadOrder(highlightId);
                const row = document.getElementById('order-row-' + highlightId);
                if (row) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.classList.add('bg-amber-50');
                    setTimeout(() => row.classList.remove('bg-amber-50'), 2000);
                }
            }
        },
        loadOrder(id) {
            fetch(`/sales-orders/${id}`)
                .then(res => res.json())
                .then(data => { this.selected = { id, ...data }; this.savedMessage = ''; });
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
