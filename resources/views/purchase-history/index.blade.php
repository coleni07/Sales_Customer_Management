@extends('layouts.app')

@php $pageTitle = 'Purchase History'; @endphp

@section('content')
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Purchase History</h1>
                @if ($customer)
                    <p class="text-sm text-gray-500 mt-1">
                        Showing orders for <span class="font-semibold text-gray-700">{{ $customer->name }}</span>
                        ({{ $customer->customer_code }})
                        &middot;
                        <a href="{{ route('purchase-history.index') }}" class="text-brand underline hover:no-underline">View all
                            customers' orders</a>
                    </p>
                @endif
            </div>
            <a href="{{ route('customers.index') }}"
                class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium transition">
                &larr; Back to Customers
            </a>
        </div>

        <div class="flex items-center gap-8 mb-5 border-b border-gray-100 pb-1">
            @foreach (['all' => 'All', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                <a href="{{ route('purchase-history.index', array_filter(['status' => $key, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'customer_id' => $customer?->id])) }}"
                    class="text-sm pb-2 -mb-px {{ $status === $key ? 'font-bold text-brand border-b-2 border-brand' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @forelse ($orders as $order)
            <div class="border border-gray-200 rounded-xl p-5 mb-4">
                <div class="flex items-start justify-between mb-4 flex-wrap gap-3">
                    <div>
                        <p class="text-sm text-gray-800">Order : <span class="font-medium">{{ $order->order_no }}</span></p>
                        <p class="text-sm text-gray-800 mt-1">Order Date : <span
                                class="font-medium">{{ $order->order_date->format('jS F Y') }}</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-[2fr_1fr_1fr] text-xs font-semibold text-gray-600 px-1 pb-2">
                    <span>Items</span>
                    <span class="text-center">Status</span>
                    <span class="text-center">Expected Delivery</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="grid grid-cols-[2fr_1fr_1fr] items-center py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-box text-lg text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $item->product?->name ?? 'Product' }}</p>
                                    <p class="text-xs text-gray-700 mt-1">
                                        Quantity: <span class="font-medium">{{ $item->qty }}</span>
                                        &nbsp;&nbsp;
                                        Price: <span class="font-semibold">₱{{ number_format($item->price, 2) }}</span>
                                    </p>
                                </div>
                            </div>
                            <p
                                class="text-center text-sm font-medium {{ $order->status === 'delivered' ? 'text-emerald-600' : ($order->status === 'cancelled' ? 'text-rose-500' : 'text-amber-500') }}">
                                {{ ucfirst($order->status) }}
                            </p>
                            <p class="text-center text-sm text-gray-700">{{ $order->order_date->addDays(5)->format('jS F Y') }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-100">
                    <p class="text-sm text-gray-700">Status: {{ ucfirst($order->status) }}</p>
                    <p class="bg-gray-100 rounded-full px-5 py-2 text-sm font-semibold text-gray-800">
                        Total Price: ₱{{ number_format($order->amount, 2) }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 py-10">No orders found for this filter.</p>
        @endforelse

        @if ($orders->hasPages())
            <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                <div class="text-xs text-gray-500">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection