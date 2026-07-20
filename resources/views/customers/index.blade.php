@extends('layouts.app')
@php $pageTitle = 'Customers'; @endphp

@section('content')

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

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
                        <th class="text-center font-bold px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 font-semibold text-gray-800">{{ $customer->customer_code }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->name }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->location }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->phone }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-gray-800">{{ $customer->total_orders }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($customer->status === 'Active')
                                    <span class="inline-block bg-green-200 text-green-800 text-xs font-semibold px-4 py-1.5 rounded-full">Active</span>
                                @else
                                    <span class="inline-block bg-red-200 text-red-700 text-xs font-semibold px-4 py-1.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('purchase-history.index', ['customer_id' => $customer->id]) }}"
                                   title="View {{ $customer->name }}'s purchase history"
                                   class="inline-block text-gray-600 hover:text-navy transition text-base">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
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

    </div>

@endsection