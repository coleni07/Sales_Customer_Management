@extends('layouts.app')

@section('title', $pageTitle)

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sales-report.css') }}">
@endpush

@section('content')
    <div class="detail-breadcrumb">
        <a href="{{ route('reports.sales') }}">Sales Report</a>
        <span class="crumb-sep"> &rsaquo; </span>
        <span>{{ $pageTitle }}</span>
    </div>

    <div class="topbar">
        <h2>{{ $pageTitle }}</h2>
        <div class="topbar-actions">
            <!-- Quick Report Switcher -->
            <div class="report-switcher"
                style="display: flex; gap: 4px; background: #F1F5F9; padding: 4px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <a href="{{ route('reports.sales.products') }}" style="padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12.5px; font-weight: 600; transition: all 0.2s;
                          background: {{ $reportKey === 'product' ? '#1E2A6E' : 'transparent' }};
                          color: {{ $reportKey === 'product' ? '#FFFFFF' : '#64748B' }};">
                    Products
                </a>
                <a href="{{ route('reports.sales.regional') }}" style="padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12.5px; font-weight: 600; transition: all 0.2s;
                          background: {{ $reportKey === 'regional' ? '#1E2A6E' : 'transparent' }};
                          color: {{ $reportKey === 'regional' ? '#FFFFFF' : '#64748B' }};">
                    Regional
                </a>
                <a href="{{ route('reports.sales.reps') }}" style="padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12.5px; font-weight: 600; transition: all 0.2s;
                          background: {{ $reportKey === 'rep' ? '#1E2A6E' : 'transparent' }};
                          color: {{ $reportKey === 'rep' ? '#FFFFFF' : '#64748B' }};">
                    Representatives
                </a>
            </div>

            <a href="{{ route('reports.sales') }}" class="date-picker">&larr; Overview</a>

            <div class="export-dropdown">
                <button type="button" class="export-btn" onclick="toggleExportMenu()">Export</button>
                <div class="export-menu" id="export-menu">
                    <a href="{{ route('reports.sales.export', ['report' => $reportKey, 'format' => 'csv']) }}">CSV
                        (.csv)</a>
                    <a href="{{ route('reports.sales.export', ['report' => $reportKey, 'format' => 'pdf']) }}">PDF
                        (.pdf)</a>
                    <a href="{{ route('reports.sales.export', ['report' => $reportKey, 'format' => 'excel']) }}">Excel
                        (.xlsx)</a>
                </div>
            </div>
        </div>
    </div>

    @yield('detail-content')
@endsection

@push('scripts')
    <script>
        function toggleExportMenu() {
            document.getElementById('export-menu').classList.toggle('open');
        }
        document.addEventListener('click', function (e) {
            const menu = document.getElementById('export-menu');
            if (menu && !e.target.closest('.export-dropdown')) menu.classList.remove('open');
        });
    </script>
@endpush