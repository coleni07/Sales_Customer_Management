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

        <div class="flex items-center gap-8 mb-5 border-b border-gray-100 pb-1">
            @foreach (['all' => 'All', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                <a href="{{ route('purchase-history.index', array_filter(['status' => $key, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'customer_id' => $customer?->id])) }}"
                   class="text-sm pb-2 -mb-px {{ $status === $key ? 'font-bold text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @forelse ($orders as $order)
            <div class="border border-gray-200 rounded-xl p-5 mb-4">

                <form method="GET" action="{{ route('purchase-history.index') }}" class="flex items-start justify-between mb-4 flex-wrap gap-3">
                    <div>
                        <p class="text-sm text-gray-800">Order : <span class="font-medium">{{ $order->order_number }}</span></p>
                        <p class="text-sm text-gray-800 mt-1">Order Payment : <span class="font-medium">{{ $order->payment_date->format('jS F Y') }}</span></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="status" value="{{ $status }}">
                        @if ($customer)
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        @endif
                    </div>
                </form>

                <div class="grid grid-cols-[2fr_1fr_1fr] text-xs font-semibold text-gray-600 px-1 pb-2">
                    <span></span>
                    <span class="text-center">Status</span>
                    <span class="text-center">Expected Delivery</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="grid grid-cols-[2fr_1fr_1fr] items-center py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <i class="fa-solid {{ $item->icon }} text-2xl text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $item->product_name }}</p>
                                    <p class="text-xs text-gray-500">Store : {{ $item->store_name }}</p>
                                    <p class="text-xs text-gray-700 mt-1">
                                        Quantity : <span class="font-medium">{{ $item->quantity }}</span>
                                        &nbsp;&nbsp;
                                        <span class="text-blue-600 font-medium">Price</span> : <span class="font-semibold">Php {{ number_format($item->price, 2) }}</span>
                                    </p>
                                </div>
                            </div>
                            <p class="text-center text-sm font-medium {{ $item->status === 'delivered' ? 'text-green-600' : 'text-red-500' }}">
                                {{ ucfirst($item->status) }}
                            </p>
                            <p class="text-center text-sm text-gray-700">{{ $item->expected_delivery->format('jS F Y') }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($status === 'completed')
                    <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-100">
                        <p class="text-sm text-gray-700">Payment is Succesfull!</p>
                        <p class="bg-gray-100 rounded-full px-5 py-2 text-sm font-semibold text-gray-800">
                            Total Price: Php {{ number_format($order->completed_total, 2) }}
                        </p>
                    </div>
                @elseif ($status === 'cancelled')
                    <div class="pt-4 mt-2 border-t border-gray-100 text-center">
                        <p class="text-sm text-gray-700">Ordered Cancelled</p>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-gray-400 py-10">No orders found for this filter.</p>
        @endforelse

    </div>

@endsection