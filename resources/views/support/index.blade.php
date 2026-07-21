@extends('layouts.app')
@php $pageTitle = 'Support System'; @endphp

@section('content')

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-3 text-sm font-medium">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{ selected: null }" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Ticket list -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

            <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" placeholder="Search Ticket ID, Customer"
                           class="w-full bg-gray-100 rounded-full pl-11 pr-4 py-2.5 text-sm text-gray-600 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>

                <button type="button" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 transition rounded-full px-5 py-2.5 text-sm font-medium text-gray-700">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800">
                            <th class="text-left font-bold px-5 py-4">Ticket ID</th>
                            <th class="text-left font-bold px-5 py-4">Customer</th>
                            <th class="text-left font-bold px-5 py-4">Subject</th>
                            <th class="text-left font-bold px-5 py-4">Priority</th>
                            <th class="text-left font-bold px-5 py-4">Status</th>
                            <th class="text-left font-bold px-5 py-4">Assigned To</th>
                            <th class="text-center font-bold px-5 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tickets as $ticket)
                            <tr @click="selected = {{ $ticket->id }}"
                                :class="selected === {{ $ticket->id }} ? 'bg-brand/5' : ''"
                                class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-5 py-4 font-semibold text-gray-800">{{ $ticket->code() }}</td>
                                <td class="px-5 py-4 text-gray-700">{{ $ticket->customer_name }}</td>
                                <td class="px-5 py-4 text-gray-700">{{ $ticket->subject }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full {{ $ticket->priorityBadgeClasses() }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full {{ $ticket->statusBadgeClasses() }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-gray-700">{{ $ticket->assigned_to }}</td>
                                <td class="px-5 py-4 text-center">
                                    <i class="fa-regular fa-eye text-gray-600"></i>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    No support tickets yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
                <span>Showing 1 to {{ $tickets->count() }} out of {{ $tickets->count() }} entries</span>
            </div>
        </div>

        <!-- Ticket detail panel -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:sticky lg:top-24">

            @foreach ($tickets as $ticket)
                <div x-show="selected === {{ $ticket->id }}" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-slate-800">Ticket {{ $ticket->code() }}</h4>
                        <button @click="selected = null" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <hr class="mb-4 border-gray-200">

                    <p class="text-xs font-semibold text-gray-500 uppercase">Customer</p>
                    <p class="text-sm text-gray-800 mb-3">{{ $ticket->customer_name }}</p>

                    <p class="text-xs font-semibold text-gray-500 uppercase">Subject</p>
                    <p class="text-sm text-gray-800 mb-3">{{ $ticket->subject }}</p>

                    <p class="text-xs font-semibold text-gray-500 uppercase">Priority</p>
                    <p class="mb-3">
                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full {{ $ticket->priorityBadgeClasses() }}">
                            {{ $ticket->priority }}
                        </span>
                    </p>

                    <p class="text-xs font-semibold text-gray-500 uppercase">Status</p>
                    <p class="mb-3">
                        <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full {{ $ticket->statusBadgeClasses() }}">
                            {{ $ticket->status }}
                        </span>
                    </p>

                    <p class="text-xs font-semibold text-gray-500 uppercase">Assigned To</p>
                    <p class="text-sm text-gray-800 mb-3">{{ $ticket->assigned_to }}</p>

                    <p class="text-xs font-semibold text-gray-500 uppercase">Date</p>
                    <p class="text-sm text-gray-800 mb-3">{{ $ticket->created_at->format('M d, Y') }}</p>

                    <hr class="mb-4 border-gray-200">

                    <h5 class="text-sm font-bold text-slate-800 mb-2">Description</h5>
                    <p class="text-sm text-gray-600 mb-4">{{ $ticket->description }}</p>

                    <a href="{{ route('support.feedback.create', $ticket) }}"
                       class="block text-center bg-brand hover:bg-brand-dark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                        Answer Invoice
                    </a>
                </div>
            @endforeach

            <div x-show="selected === null" class="text-center py-10">
                <i class="fa-solid fa-ticket fa-3x text-gray-300 mb-4"></i>
                <h5 class="font-semibold text-slate-800">Select a Ticket</h5>
                <p class="text-gray-500 text-sm mt-1">
                    Click any ticket from the table to view its details.
                </p>
            </div>
        </div>

    </div>

@endsection
