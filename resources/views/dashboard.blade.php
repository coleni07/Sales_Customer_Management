@extends('layouts.app')

@php $pageTitle = 'Dashboard'; @endphp

@section('content')
<div class="space-y-6">

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
            <p class="text-sm text-slate-500">Total Sales</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">₱ {{ number_format($totalSales, 2) }}</p>
            <p class="text-xs mt-2 {{ $salesGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                {{ $salesGrowth >= 0 ? '+' : '' }}{{ $salesGrowth }}% from last month
            </p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
            <p class="text-sm text-slate-500">Total Orders</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalOrders) }}</p>
            <p class="text-xs mt-2 text-emerald-500">+{{ $ordersGrowth }}% from last month</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
            <p class="text-sm text-slate-500">Total Customers</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalCustomers) }}</p>
            <p class="text-xs mt-2 text-emerald-500">+{{ $customersGrowth }}% from last month</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow-sm card-hover" x-data="{ open: false, range: 'This Week' }">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-semibold text-slate-800">Sales Overview</h2>
                <div class="relative">
                    <button @click="open = !open" class="text-sm px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-1 hover:bg-slate-50 transition-colors">
                        <span x-text="range"></span>
                        <svg viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                         class="absolute right-0 mt-2 w-36 bg-white rounded-lg shadow-lg border border-slate-100 py-1 z-10">
                        <button @click="range = 'This Week'; open = false; updateSalesChart('This Week')" class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50">This Week</button>
                        <button @click="range = 'This Month'; open = false; updateSalesChart('This Month')" class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50">This Month</button>
                        <button @click="range = 'This Year'; open = false; updateSalesChart('This Year')" class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50">This Year</button>
                    </div>
                </div>
            </div>
            <canvas id="salesOverviewChart" height="110"></canvas>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
            <h2 class="font-semibold text-slate-800 mb-2">Orders by Status</h2>
            <div class="flex items-center justify-center">
                <canvas id="ordersByStatusChart" width="240" height="240"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
            <h2 class="font-semibold text-slate-800 mb-3">Recent Orders</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-slate-100">
                            <th class="py-2 font-medium">Order ID</th>
                            <th class="py-2 font-medium">Customer</th>
                            <th class="py-2 font-medium">Product</th>
                            <th class="py-2 font-medium">Total</th>
                            <th class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr class="border-b border-slate-50 hover:bg-slate-50">
                                <td class="py-2.5"><a href="{{ route('sales-orders.index') }}" class="text-brand-dark font-medium hover:underline">{{ $order->order_no }}</a></td>
                                <td class="py-2.5">{{ $order->customer->name }}</td>
                                <td class="py-2.5">{{ $order->items->first()->item_name ?? '—' }}</td>
                                <td class="py-2.5">₱{{ number_format($order->amount, 2) }}</td>
                                <td class="py-2.5">
                                    <span class="badge-in text-xs px-2.5 py-1 rounded-full font-medium {{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm card-hover">
            <h2 class="font-semibold text-slate-800 mb-3">Latest Tickets</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-slate-100">
                            <th class="py-2 font-medium">Ticket ID</th>
                            <th class="py-2 font-medium">Customer</th>
                            <th class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestTickets as $ticket)
                            <tr class="border-b border-slate-50 hover:bg-slate-50">
                                <td class="py-2.5 font-medium text-brand-dark">{{ $ticket->ticket_no }}</td>
                                <td class="py-2.5">{{ $ticket->customer->name }}</td>
                                <td class="py-2.5">
                                    <span class="badge-in text-xs px-2.5 py-1 rounded-full font-medium {{ $ticket->statusColor() }}">{{ $ticket->statusLabel() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let salesChart;

const salesRanges = {
    'This Week': {
        labels: @json(collect($salesByDay)->pluck('label')),
        data: @json(collect($salesByDay)->pluck('total')),
    },
    'This Month': {
        labels: @json(collect($salesByWeek)->pluck('label')),
        data: @json(collect($salesByWeek)->pluck('total')),
    },
    'This Year': {
        labels: @json(collect($salesByMonth)->pluck('label')),
        data: @json(collect($salesByMonth)->pluck('total')),
    },
};

function updateSalesChart(range) {
    const set = salesRanges[range];
    salesChart.data.labels = set.labels;
    salesChart.data.datasets[0].data = set.data;
    salesChart.update();
}

document.addEventListener('DOMContentLoaded', function () {
    salesChart = new Chart(document.getElementById('salesOverviewChart'), {
        type: 'line',
        data: {
            labels: salesRanges['This Week'].labels,
            datasets: [{
                label: 'Sales',
                data: salesRanges['This Week'].data,
                borderColor: '#0FA98E',
                backgroundColor: 'rgba(15,169,142,0.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#0FA98E',
            }]
        },
        options: {
            animation: { duration: 900, easing: 'easeOutQuart' },
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => '₱' + (v / 1000) + 'K' }, grid: { color: '#F1F5F9' } },
                x: { grid: { display: false } },
            }
        }
    });

    new Chart(document.getElementById('ordersByStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: @json(array_values($ordersByStatus)),
                backgroundColor: ['#FBBF24', '#3B82F6', '#8B5CF6', '#10B981', '#F43F5E'],
                borderWidth: 0,
            }]
        },
        options: {
            animation: { animateRotate: true, duration: 900 },
            cutout: '65%',
            plugins: {
                legend: { display: false },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 11 },
                    textAlign: 'center',
                    formatter: (value, ctx) => {
                        const pct = @json($ordersByStatusPct->values());
                        const labels = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                        const i = ctx.dataIndex;
                        return pct[i] > 0 ? [labels[i], pct[i] + '%'] : '';
                    },
                }
            },
        },
        plugins: [ChartDataLabels],
    });
});
</script>
@endsection
