@extends('layouts.app')
@php $pageTitle = 'Support System'; @endphp

@section('content')

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)" x-transition
             class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-3 text-sm font-medium">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @php
        // Lightweight JSON snapshot of the tickets for the Alpine.js table
        // below. Badge classes are precomputed here (via the existing model
        // helpers) so the JS side never has to re-implement that logic.
        $ticketsData = $tickets->map(fn ($t) => [
            'id' => $t->id,
            'code' => $t->code(),
            'customer_name' => $t->customer_name,
            'subject' => $t->subject,
            'priority' => $t->priority,
            'priority_badge' => $t->priorityBadgeClasses(),
            'status' => $t->status,
            'status_badge' => $t->statusBadgeClasses(),
            'assigned_to' => $t->assigned_to,
            'created_at' => optional($t->created_at)->toIso8601String(),
        ])->values();
    @endphp

    <div
        x-data="{
            selected: null,
            search: '',
            sortBy: 'default',
            showFilter: false,
            tickets: {{ Js::from($ticketsData) }},
            get filteredTickets() {
                let list = this.tickets;

                const q = this.search.trim().toLowerCase();
                if (q !== '') {
                    list = list.filter(t =>
                        t.code.toLowerCase().includes(q) ||
                        t.customer_name.toLowerCase().includes(q) ||
                        t.subject.toLowerCase().includes(q)
                    );
                }

                list = [...list];
                if (this.sortBy === 'az') {
                    list.sort((a, b) => a.customer_name.localeCompare(b.customer_name));
                } else if (this.sortBy === 'za') {
                    list.sort((a, b) => b.customer_name.localeCompare(a.customer_name));
                } else if (this.sortBy === 'date_new') {
                    list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                } else if (this.sortBy === 'date_old') {
                    list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                }

                return list;
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
                // Deep-link support: /support?ticket=5 auto-opens that
                // ticket's detail panel, so notification links can jump
                // straight to the relevant record instead of just the page.
                const params = new URLSearchParams(window.location.search);
                const ticketId = params.get('ticket');
                if (ticketId) {
                    this.selected = parseInt(ticketId, 10);
                    this.$nextTick(() => {
                        const row = document.getElementById('ticket-row-' + ticketId);
                        if (row) {
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            }
        }"
        x-init="init()"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

        <!-- Ticket list -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

            <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
                <div class="relative flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" x-model="search" placeholder="Search Ticket ID, Customer"
                           class="w-full bg-gray-100 rounded-full pl-11 pr-4 py-2.5 text-sm text-gray-600 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>

                <div class="relative" @click.outside="showFilter = false">
                    <button type="button" @click="showFilter = !showFilter"
                            class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 transition rounded-full px-5 py-2.5 text-sm font-medium text-gray-700">
                        <i class="fa-solid fa-filter"></i>
                        <span x-text="sortLabel()"></span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>

                    <div x-show="showFilter" x-cloak x-transition
                         class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-10 text-sm">
                        <button type="button" @click="sortBy = 'az'; showFilter = false"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2"
                                :class="sortBy === 'az' ? 'text-brand font-semibold' : 'text-gray-700'">
                            <i class="fa-solid fa-arrow-down-a-z w-4"></i> A - Z
                        </button>
                        <button type="button" @click="sortBy = 'za'; showFilter = false"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2"
                                :class="sortBy === 'za' ? 'text-brand font-semibold' : 'text-gray-700'">
                            <i class="fa-solid fa-arrow-down-z-a w-4"></i> Z - A
                        </button>
                        <button type="button" @click="sortBy = 'date_new'; showFilter = false"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2"
                                :class="sortBy === 'date_new' ? 'text-brand font-semibold' : 'text-gray-700'">
                            <i class="fa-solid fa-calendar w-4"></i> Date: Newest first
                        </button>
                        <button type="button" @click="sortBy = 'date_old'; showFilter = false"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2"
                                :class="sortBy === 'date_old' ? 'text-brand font-semibold' : 'text-gray-700'">
                            <i class="fa-solid fa-calendar w-4"></i> Date: Oldest first
                        </button>
                        <hr class="my-2 border-gray-100">
                        <button type="button" @click="sortBy = 'default'; showFilter = false"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-500">
                            <i class="fa-solid fa-rotate-left w-4"></i> Reset
                        </button>
                    </div>
                </div>
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
                        <template x-for="ticket in filteredTickets" :key="ticket.id">
                            <tr @click="selected = ticket.id"
                                :id="'ticket-row-' + ticket.id"
                                :class="selected === ticket.id ? 'bg-brand/5' : ''"
                                class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-5 py-4 font-semibold text-gray-800" x-text="ticket.code"></td>
                                <td class="px-5 py-4 text-gray-700" x-text="ticket.customer_name"></td>
                                <td class="px-5 py-4 text-gray-700" x-text="ticket.subject"></td>
                                <td class="px-5 py-4">
                                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full"
                                          :class="ticket.priority_badge" x-text="ticket.priority"></span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full"
                                          :class="ticket.status_badge" x-text="ticket.status"></span>
                                </td>
                                <td class="px-5 py-4 text-gray-700" x-text="ticket.assigned_to"></td>
                                <td class="px-5 py-4 text-center">
                                    <i class="fa-regular fa-eye text-gray-600"></i>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="tickets.length === 0">
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                No support tickets yet.
                            </td>
                        </tr>
                        <tr x-show="tickets.length > 0 && filteredTickets.length === 0">
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                No tickets match "<span x-text="search"></span>".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
                <span>Showing <span x-text="filteredTickets.length"></span> out of <span x-text="tickets.length"></span> entries</span>
            </div>
        </div>

        <!-- Ticket detail panel -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:sticky lg:top-24 flex flex-col h-full">

            @foreach ($tickets as $ticket)
                <div x-show="selected === {{ $ticket->id }}" x-cloak class="flex flex-col h-full">
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
                    <p class="text-sm text-gray-600 mb-4 flex-1">{{ $ticket->description }}</p>

                    <a href="{{ route('support.feedback.create', $ticket) }}"
                       class="mt-auto block text-center bg-brand hover:bg-brand-dark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                        Answer Invoice
                    </a>
                </div>
            @endforeach

            <div x-show="selected === null" class="flex-1 flex flex-col items-center justify-center text-center py-10">
                <i class="fa-solid fa-ticket fa-3x text-gray-300 mb-4"></i>
                <h5 class="font-semibold text-slate-800">Select a Ticket</h5>
                <p class="text-gray-500 text-sm mt-1">
                    Click any ticket from the table to view its details.
                </p>
            </div>
        </div>

    </div>

@endsection
