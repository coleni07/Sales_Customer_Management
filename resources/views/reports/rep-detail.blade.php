@extends('layouts.report-detail')

@section('detail-content')

<div class="report-card">
    <div class="filter-row">
        <input type="text" id="rep-search" class="search-input" placeholder="Search representatives..." onkeyup="filterReps()">
        <div class="chip-row" id="region-chips">
            <button class="chip active" data-region="all" onclick="setRegionFilter('all', this)">All Regions</button>
            @foreach ($regions as $region)
                <button class="chip" data-region="{{ $region['name'] }}" onclick="setRegionFilter('{{ $region['name'] }}', this)">{{ $region['name'] }}</button>
            @endforeach
        </div>
    </div>

    <table>
        <tr><th></th><th>Rank</th><th>Representative</th><th>Region</th><th>Revenue</th><th>Quota</th><th>Trend</th></tr>
        @php $medals = ['🥇', '🥈', '🥉']; @endphp
        <tbody id="rep-table-body">
        @foreach ($reps->sortByDesc('revenue')->values() as $i => $rep)
            @php $regionColor = $regions->keyBy('name')[$rep['region']]['color']; @endphp
            <tr class="rep-row" data-region="{{ $rep['region'] }}" data-name="{{ strtolower($rep['name']) }}" onclick="toggleExpand('rep-{{ $i }}')">
                <td><span class="expand-caret" id="caret-rep-{{ $i }}">▸</span></td>
                <td><div class="name-cell"><span class="rank-badge">{{ $medals[$i] ?? $i + 1 }}</span></div></td>
                <td><div class="name-cell">{{ $rep['name'] }}</div></td>
                <td><span class="region-tag"><span class="dot" style="background:{{ $regionColor }}"></span>{{ $rep['region'] }}</span></td>
                <td>₱{{ number_format($rep['revenue']) }}</td>
                <td>
                    <div class="quota-bar-track">
                        <div class="quota-bar-fill" style="width:{{ min($rep['quotaPercent'], 100) }}%; background:{{ $rep['quotaPercent'] >= 100 ? '#1FAE6C' : '#3B82F6' }};"></div>
                    </div>
                    <span class="quota-bar-label">{{ $rep['quotaPercent'] }}%</span>
                </td>
                <td>
                    @if ($rep['change'] >= 0)
                        <span class="status-pill pill-good">▲ +{{ $rep['change'] }}%</span>
                    @else
                        <span class="status-pill pill-bad">▼ {{ $rep['change'] }}%</span>
                    @endif
                </td>
            </tr>
            <tr class="expand-row" id="rep-{{ $i }}" style="display:none;">
                <td colspan="7">
                    <div class="expand-panel">
                        <div class="expand-col">
                            <span class="expand-label">Quota Trend (6mo)</span>
                            @php
                                $trend = $rep['quotaTrend'];
                                $max = max($trend) ?: 1;
                                $pts = collect($trend)->map(function ($v, $idx) use ($trend, $max) {
                                    $x = $idx * (200 / (count($trend) - 1));
                                    $y = 45 - ($v / $max * 40);
                                    return "$x,$y";
                                })->implode(' ');
                            @endphp
                            <svg viewBox="0 0 200 50" class="mini-sparkline" preserveAspectRatio="none">
                                <polyline points="{{ $pts }}" fill="none" stroke="{{ $regionColor }}" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="expand-col">
                            <span class="expand-label">Product Mix</span>
                            @foreach ($rep['productMix'] as $p)
                                <div class="mini-bar-row">
                                    <span class="mini-bar-label">{{ $p['name'] }}</span>
                                    <div class="mini-bar-track"><div class="mini-bar-fill" style="width:{{ $p['percent'] }}%; background:{{ $regionColor }};"></div></div>
                                    <span class="mini-bar-pct">{{ $p['percent'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="expand-col">
                            <span class="expand-label">Regional Coverage</span>
                            @foreach ($rep['coverage'] as $regionName => $pct)
                                @php $rc = $regions->keyBy('name')[$regionName]['color']; @endphp
                                <div class="mini-bar-row">
                                    <span class="mini-bar-label">{{ $regionName }}</span>
                                    <div class="mini-bar-track"><div class="mini-bar-fill" style="width:{{ $pct }}%; background:{{ $rc }};"></div></div>
                                    <span class="mini-bar-pct">{{ $pct }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
    function toggleExpand(id) {
        const row = document.getElementById(id);
        const caret = document.getElementById('caret-' + id);
        const isOpen = row.style.display !== 'none';
        row.style.display = isOpen ? 'none' : 'table-row';
        if (caret) caret.textContent = isOpen ? '▸' : '▾';
    }

    window.activeRegion = 'all';

    function filterReps() {
        const term = document.getElementById('rep-search').value.toLowerCase();
        document.querySelectorAll('.rep-row').forEach(row => {
            const matchesSearch = row.dataset.name.includes(term);
            const matchesRegion = window.activeRegion === 'all' || row.dataset.region === window.activeRegion;
            const show = matchesSearch && matchesRegion;
            row.style.display = show ? '' : 'none';
            const expandRow = row.nextElementSibling;
            if (!show && expandRow) expandRow.style.display = 'none';
        });
    }

    function setRegionFilter(region, btn) {
        window.activeRegion = region;
        document.querySelectorAll('#region-chips .chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        filterReps();
    }
</script>
@endpush
