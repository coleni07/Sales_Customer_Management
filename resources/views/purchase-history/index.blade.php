@extends('layouts.plain')

@section('title', 'Order History')

@section('content')

    <div class="p-8">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 leading-snug">Purchase<br>History</h1>
                @if ($customer)
                    <p class="text-sm text-gray-500 mt-2">
                        Showing orders for <span class="font-semibold text-gray-700">{{ $customer->name }}</span> ({{ $customer->customer_code }})
                        &middot;
                        <a href="{{ route('purchase-history.index') }}" class="text-navy underline hover:no-underline">View all customers' orders</a>
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}" class="w-9 h-9 rounded-full bg-black text-white flex items-center justify-center hover:opacity-80 transition">
                    <i class="fa-solid fa-house text-sm"></i>
                </a>
                <button class="w-9 h-9 rounded-full bg-black text-white flex items-center justify-center hover:opacity-80 transition">
                    <i class="fa-solid fa-gear text-sm"></i>
                </button>
            </div>
        </div>

        @if ($customer)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-5 flex flex-wrap items-center justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-x-8 gap-y-2">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $customer->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone</p>
                        <p class="text-sm text-gray-800 mt-0.5 font-mono">{{ $customer->formatted_phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Address</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $customer->address ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $customer->location ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $customer->status }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $summary['orders_count'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Orders Shown</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $summary['items_count'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Items Purchased</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">Php {{ number_format($summary['total_spent'], 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Spent (Delivered)</p>
            </div>
        </div>

        <div class="flex items-center gap-8 mb-5 border-b border-gray-100 pb-1">
            @foreach (['all' => 'All', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                <a href="{{ route('purchase-history.index', array_filter(['status' => $key, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'customer_id' => $customer?->id])) }}"
                   class="text-sm pb-2 -mb-px {{ $status === $key ? 'font-bold text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @php
            $statusStyles = [
                'delivered' => 'bg-green-100 text-green-700',
                'shipped' => 'bg-sky-100 text-sky-700',
                'processing' => 'bg-amber-100 text-amber-700',
                'pending' => 'bg-gray-100 text-gray-600',
                'cancelled' => 'bg-red-100 text-red-600',
            ];
        @endphp

        @forelse ($orders as $order)
            <div class="border border-gray-200 rounded-xl p-5 mb-4">

                <div class="flex items-start justify-between mb-4 flex-wrap gap-3">
                    <div>
                        <p class="text-sm text-gray-800">Order : <span class="font-medium">{{ $order->order_no }}</span></p>
                        <p class="text-sm text-gray-800 mt-1">Order Date : <span class="font-medium">{{ $order->order_date->format('jS F Y') }}</span></p>
                    </div>
                    <span class="inline-block text-xs font-semibold px-4 py-1.5 rounded-full {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-[2fr_1fr_1fr] text-xs font-semibold text-gray-600 px-1 pb-2">
                    <span>Product</span>
                    <span class="text-center">Quantity</span>
                    <span class="text-right">Price</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="grid grid-cols-[2fr_1fr_1fr] items-center py-4">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->product->category ?? '' }}</p>
                            </div>
                            <p class="text-center text-sm text-gray-700">{{ $item->qty }}</p>
                            <p class="text-right text-sm font-semibold text-gray-800">Php {{ number_format($item->price, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-100">
                    <p class="text-sm text-gray-700">
                        @if ($order->status === 'delivered')
                            Payment is Successful!
                        @elseif ($order->status === 'cancelled')
                            Order Cancelled
                        @else
                            Order {{ ucfirst($order->status) }}
                        @endif
                    </p>
                    <p class="bg-gray-100 rounded-full px-5 py-2 text-sm font-semibold text-gray-800">
                        Total Price: Php {{ number_format($order->amount, 2) }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 py-10">No orders found for this filter.</p>
        @endforelse

    </div>

@endsection
