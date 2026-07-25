@extends('layouts.app')
@php $pageTitle = $customer->name; @endphp

@section('content')

    <div class="mb-6">
        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-navy transition">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Customers
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Profile card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-navy/10 flex items-center justify-center text-navy text-2xl font-bold mb-4">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $customer->customer_code }}</p>

                    @if ($customer->status === 'Active')
                        <span class="inline-block bg-green-200 text-green-800 text-xs font-semibold px-4 py-1.5 rounded-full mt-3">Active</span>
                    @else
                        <span class="inline-block bg-red-200 text-red-700 text-xs font-semibold px-4 py-1.5 rounded-full mt-3">Inactive</span>
                    @endif
                </div>

                <dl class="space-y-4 text-sm">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-phone w-5 text-gray-400 mt-0.5"></i>
                        <div>
                            <dt class="text-gray-400 text-xs">Phone</dt>
                            <dd class="text-gray-800 font-medium">{{ $customer->phone }}</dd>
                        </div>
                    </div>

                    @if ($customer->email)
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-envelope w-5 text-gray-400 mt-0.5"></i>
                            <div>
                                <dt class="text-gray-400 text-xs">Email</dt>
                                <dd class="text-gray-800 font-medium">{{ $customer->email }}</dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot w-5 text-gray-400 mt-0.5"></i>
                        <div>
                            <dt class="text-gray-400 text-xs">Location</dt>
                            <dd class="text-gray-800 font-medium">{{ $customer->location }}</dd>
                        </div>
                    </div>

                    @if ($customer->address)
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-house w-5 text-gray-400 mt-0.5"></i>
                            <div>
                                <dt class="text-gray-400 text-xs">Address</dt>
                                <dd class="text-gray-800 font-medium">{{ $customer->address }}</dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-cart-shopping w-5 text-gray-400 mt-0.5"></i>
                        <div>
                            <dt class="text-gray-400 text-xs">Total Orders</dt>
                            <dd class="text-gray-800 font-medium">{{ $customer->total_orders }}</dd>
                        </div>
                    </div>
                </dl>

                <a href="{{ route('purchase-history.index', ['customer_id' => $customer->id]) }}"
                   class="mt-6 block text-center bg-navy hover:bg-navyDark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                    View Full Purchase History
                </a>
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
                    <a href="{{ route('purchase-history.index', ['customer_id' => $customer->id]) }}" class="text-sm text-navy hover:underline">
                        See all &rarr;
                    </a>
                </div>

                @forelse ($orders as $order)
                    <div class="border border-gray-100 rounded-xl p-4 mb-4 last:mb-0">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-gray-800">Order : <span class="font-medium">{{ $order->order_number }}</span></p>
                            <p class="text-sm text-gray-500">{{ $order->payment_date->format('jS F Y') }}</p>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                            <i class="fa-solid {{ $item->icon }} text-gray-500"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 text-sm">{{ $item->product_name }}</p>
                                            <p class="text-xs text-gray-500">Store : {{ $item->store_name }} &nbsp;&middot;&nbsp; Qty: {{ $item->quantity }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-800">Php {{ number_format($item->price, 2) }}</p>
                                        <p class="text-xs font-medium {{ $item->status === 'delivered' ? 'text-green-600' : 'text-red-500' }}">
                                            {{ ucfirst($item->status) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-10">No orders yet for this customer.</p>
                @endforelse
            </div>
        </div>

    </div>

@endsection
