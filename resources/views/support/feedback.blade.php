@extends('layouts.app')
@php $pageTitle = 'Feedback'; @endphp

@section('content')

    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

        <div class="mb-6">
            <p class="text-xs font-semibold text-gray-500 uppercase">Replying to</p>
            <h2 class="text-lg font-bold text-slate-800">{{ $ticket->code() }} &middot; {{ $ticket->subject }}</h2>
            <p class="text-sm text-gray-500">{{ $ticket->customer_name }}</p>
        </div>

        <form action="{{ route('support.feedback.store') }}" method="POST">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

            <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
                <h1 class="text-xl font-bold text-slate-800">Feedback</h1>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Enter Title" required
                       class="flex-1 min-w-[220px] bg-gray-100 rounded-full px-4 py-2.5 text-sm text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-navy/30">
            </div>
            @error('title') <p class="text-rose-600 text-xs mb-3">{{ $message }}</p> @enderror

            <textarea name="description" rows="6" placeholder="Enter Description" required
                      class="w-full bg-gray-100 rounded-2xl px-4 py-3 text-sm text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-navy/30">{{ old('description') }}</textarea>
            @error('description') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('support.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 transition text-gray-700 font-semibold rounded-full px-6 py-2.5 text-sm">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-brand hover:bg-brand-dark transition text-white font-semibold rounded-full px-6 py-2.5 text-sm">
                    Submit Invoice
                </button>
            </div>
        </form>
    </div>

@endsection
