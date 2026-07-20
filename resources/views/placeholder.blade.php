@extends('layouts.app')

@php $pageTitle = $title; @endphp

@section('content')
<div class="bg-white rounded-xl p-10 shadow-sm card-hover flex flex-col items-center justify-center text-center min-h-[50vh]">
    <div class="w-16 h-16 rounded-full bg-brand/10 text-brand-dark flex items-center justify-center mb-4">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-8 h-8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
    </div>
    <h2 class="text-xl font-bold text-slate-800">{{ $title }}</h2>
    <p class="text-slate-500 mt-2 max-w-sm">
        This section is wired up and routable.<br> 
        Just huwaiting! #ComingSoon:)
    </p>
    <a href="{{ route('dashboard') }}" class="mt-6 px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-dark transition-colors">
        Back to Dashboard
    </a>
</div>
@endsection
