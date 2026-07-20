@extends('layouts.report-detail')

@section('detail-content')

<div class="report-card">
    <h3>Regional Sales Comparison</h3>
    <div class="legend" style="margin-bottom:14px;">
        <span><span class="dot" style="background:#14B8A6"></span>Luzon</span>
        <span><span class="dot" style="background:#3B82F6"></span>Visayas</span>
        <span><span class="dot" style="background:#F5B301"></span>Mindanao</span>
    </div>

    <div style="height:300px;">
        <canvas id="regionalChart"></canvas>
    </div>

    <div class="region-stats-row">
        @foreach ($regions as $region)
            <div class="region-stat-box" id="region-card-{{ $region['name'] }}"
                 onmouseenter="highlightRegion('{{ $region['name'] }}')" onmouseleave="unhighlightRegion('{{ $region['name'] }}')">
                <div class="region-stat-top">
                    <span class="dot" style="background:{{ $region['color'] }}"></span>
                    <span class="region-card-name">{{ $region['name'] }}</span>
                </div>
                <div class="region-stat-line"><span class="stat-label">Best Day</span><span class="stat-value" id="stat-best-{{ $region['name'] }}">—</span></div>
                <div class="region-stat-line"><span class="stat-label">Average</span><span class="stat-value" id="stat-avg-{{ $region['name'] }}">—</span></div>
                <div class="region-stat-line"><span class="stat-label">Period Total</span><span class="stat-value" id="stat-total-{{ $region['name'] }}">—</span></div>
            </div>
        @endforeach
    </div>
</div>

<div class="report-card">
    <h3>Region Breakdown — Products &amp; Representatives</h3>
    @foreach ($regions as $region)
        <div class="accordion-item">
            <div class="accordion-header" onclick="toggleExpand('region-{{ $region['name'] }}')">
                <span class="expand-caret" id="caret-region-{{ $region['name'] }}">▸</span>
                <span class="dot" style="background:{{ $region['color'] }}"></span>
                <span class="accordion-title">{{ $region['name'] }}</span>
                <span class="status-pill pill-{{ $region['percent'] >= 70 ? 'good' : 'bad' }}">{{ $region['percent'] }}% of target</span>
            </div>
            <div class="accordion-body" id="region-{{ $region['name'] }}" style="display:none;">
                <div class="expand-panel">
                    <div class="expand-col">
                        <span class="expand-label">Top Products in {{ $region['name'] }}</span>
                        @foreach ($region['topProducts'] as $p)
                            @php $maxVal = $region['topProducts']->max('value') ?: 1; @endphp
                            <div class="mini-bar-row">
                                <span class="mini-bar-label">{{ $p['name'] }}</span>
                                <div class="mini-bar-track"><div class="mini-bar-fill" style="width:{{ round($p['value']/$maxVal*100) }}%; background:{{ $region['color'] }};"></div></div>
                                <span class="mini-bar-pct">₱{{ number_format($p['value']) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="expand-col">
                        <span class="expand-label">Assigned Representatives</span>
                        @forelse ($region['reps'] as $rep)
                            <div class="expand-tag">{{ $rep['name'] }} — ₱{{ number_format($rep['revenue']) }}</div>
                        @empty
                            <div class="expand-tag" style="color:var(--red);">No rep currently assigned</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
    function toggleExpand(id) {
        const row = document.getElementById(id);
        const caret = document.getElementById('caret-' + id);
        const isOpen = row.style.display !== 'none';
        row.style.display = isOpen ? 'none' : 'block';
        if (caret) caret.textContent = isOpen ? '▸' : '▾';
    }

    const peso = (v) => '₱' + Number(v).toLocaleString('en-PH');
    const regionalData = @json($regionalChart);
    const regionNames = Object.keys(regionalData.series);

    regionNames.forEach(function (region) {
        const s = regionalData.series[region].stats;
        document.getElementById('stat-best-' + region).textContent = peso(s.bestValue) + ' · ' + s.bestLabel;
        document.getElementById('stat-avg-' + region).textContent = peso(s.average);
        document.getElementById('stat-total-' + region).textContent = peso(s.total);
    });

    let regionalChart;
    const regionalCanvas = document.getElementById('regionalChart');

    if (regionalCanvas) {
        regionalChart = new Chart(regionalCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: regionalData.labels,
                datasets: regionNames.map(function (region) {
                    return {
                        label: region,
                        data: regionalData.series[region].data,
                        borderColor: regionalData.series[region].color,
                        backgroundColor: regionalData.series[region].color + '15',
                        borderWidth: 2.5,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: false,
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (item) => item.dataset.label + ': ' + peso(item.raw) } }
                },
                scales: {
                    y: { grid: { color: '#E7E9F2' }, ticks: { callback: (v) => '₱' + (v / 1000).toFixed(1) + 'K', font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        regionalCanvas.addEventListener('mousemove', function (e) {
            const points = regionalChart.getElementsAtEventForMode(e, 'nearest', { intersect: false }, false);
            if (!points.length) return;
            highlightRegion(regionNames[points[0].datasetIndex]);
        });
        regionalCanvas.addEventListener('mouseleave', function () {
            regionNames.forEach(r => clearRegionHighlight(r));
            resetLineEmphasis();
        });
    }

    function setLineEmphasis(activeName) {
        if (!regionalChart) return;
        regionalChart.data.datasets.forEach(ds => { ds.borderWidth = (ds.label === activeName) ? 4 : 1.5; });
        regionalChart.update('none');
    }
    function resetLineEmphasis() {
        if (!regionalChart) return;
        regionalChart.data.datasets.forEach(ds => ds.borderWidth = 2.5);
        regionalChart.update('none');
    }
    function clearRegionHighlight(name) {
        const card = document.getElementById('region-card-' + name);
        if (card) card.classList.remove('region-hover');
    }
    function highlightRegion(name) {
        regionNames.forEach(r => r === name
            ? document.getElementById('region-card-' + r)?.classList.add('region-hover')
            : clearRegionHighlight(r));
        setLineEmphasis(name);
    }
    function unhighlightRegion(name) {
        clearRegionHighlight(name);
        resetLineEmphasis();
    }
</script>
@endpush
