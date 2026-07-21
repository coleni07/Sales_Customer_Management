@extends('layouts.app')

@php $pageTitle = 'Sales Report'; @endphp

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sales-report.css') }}">
@endpush

@section('content')

<div class="topbar">
    <h2>Sales Report</h2>
    <div class="topbar-actions">
        <button type="button" class="export-btn" onclick="openExportModal()">⬇ Export</button>
    </div>
</div>

{{-- KPI ROW --}}
<div class="kpi-row">
    <div class="card kpi-clickable accent-total" onclick="openKpiModal('total')">
        <div class="card-icon icon-total">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9 9.5c0-1.1 1.34-2 3-2s3 .9 3 2c0 1.5-1.5 1.8-3 2.2-1.5.4-3 .7-3 2.3 0 1.1 1.34 2 3 2s3-.9 3-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div class="card-label">Total Sales <span class="delta">+{{ $totalSales['change'] }}%</span></div>
        <div class="num">₱ {{ number_format($totalSales['value'], 2) }}</div>
        <div class="progress-track">
            <div class="progress-fill" style="width: {{ $totalSales['percent'] }}%"></div>
        </div>
        <div class="progress-caption">{{ $totalSales['percent'] }}% of ₱{{ number_format($totalSales['target'], 2) }} monthly target reached</div>

        <div class="breakdown-bar">
            @foreach ($totalSales['breakdown'] as $b)
                <div class="breakdown-segment" style="width: {{ $b['percent'] }}%; background: {{ $b['color'] }};"></div>
            @endforeach
        </div>
        <div class="breakdown-legend">
            @foreach ($totalSales['breakdown'] as $b)
                <span><span class="dot" style="background: {{ $b['color'] }}"></span>{{ $b['category'] }} · {{ $b['percent'] }}%</span>
            @endforeach
        </div>
    </div>

    <div class="card gauge-card kpi-clickable accent-target" onclick="openKpiModal('target')">
        <div class="card-icon icon-target">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
        </div>
        <div class="card-label" style="width:100%">Target Achievement</div>
        <div class="gauge-wrap">
            <svg width="110" height="110" viewBox="0 0 110 110">
                <circle cx="55" cy="55" r="46" fill="none" stroke="#E7E9F2" stroke-width="12"/>
                <circle cx="55" cy="55" r="46" fill="none" stroke="#1FAE6C" stroke-width="12"
                    stroke-dasharray="289" stroke-dashoffset="{{ 289 - (289 * ($totalSales['percent'] ?? 0) / 100) }}" stroke-linecap="round"
                    transform="rotate(-90 55 55)"/>
            </svg>
            <div class="gauge-value">{{ $totalSales['percent'] }}%</div>
        </div>
        <div class="progress-caption">On track for target</div>
    </div>

    <div class="card kpi-clickable accent-forecast" onclick="openKpiModal('forecast')">
        <div class="card-icon icon-forecast">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 16l5-6 4 3 7-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 4h5v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="card-label">Sales Forecast Next Month <span class="delta">+{{ $forecast['change'] }}%</span></div>
        <div class="num">₱ {{ number_format($forecast['value'], 2) }}</div>
        <svg class="sparkline" viewBox="0 0 260 46" preserveAspectRatio="none">
            <polyline points="0,36 40,28 80,32 120,14 160,18" fill="none" stroke="#1E2A6E" stroke-width="3" stroke-linecap="round"/>
            <polyline points="160,18 200,10 260,4" fill="none" stroke="#1FAE6C" stroke-width="3" stroke-dasharray="5,5" stroke-linecap="round"/>
        </svg>
        <div class="forecast-sub">Best case ₱{{ number_format($forecast['best']) }}K · Worst case ₱{{ number_format($forecast['worst']) }}K</div>
    </div>
</div>

{{-- REVENUE OVERVIEW --}}
<div class="card overview-card">
    <div class="overview-head">
        <h3>Revenue Overview &amp; Forecast</h3>
        <div class="range-toggle">
            <button class="range-btn active" data-range="7D" onclick="setChartRange('7D', this)">7D</button>
            <button class="range-btn" data-range="30D" onclick="setChartRange('30D', this)">30D</button>
            <button class="range-btn" data-range="3M" onclick="setChartRange('3M', this)">3M</button>
            <button class="range-btn" data-range="6M" onclick="setChartRange('6M', this)">6M</button>
        </div>
    </div>

    <div class="legend" style="margin-bottom:14px;">
        <span><span class="dot" style="background:#1E2A6E"></span>Actual</span>
        <span><span class="dot" style="background:#1FAE6C"></span>Forecast</span>
        <span><span class="dot" style="background:#B7BCD6"></span>Previous Period</span>
    </div>

    <div class="stats-strip">
        <div class="stat-block">
            <span class="stat-label">Best Day</span>
            <span class="stat-value" id="stat-best">—</span>
        </div>
        <div class="stat-block">
            <span class="stat-label">Average</span>
            <span class="stat-value" id="stat-average">—</span>
        </div>
        <div class="stat-block">
            <span class="stat-label">Period Total</span>
            <span class="stat-value" id="stat-total">—</span>
        </div>
    </div>

    <div style="height:220px;">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

{{-- TABS --}}
<div class="tabs">
    <button class="tab-btn active" data-tab="product" onclick="switchTab('product', this)">Product Report</button>
    <button class="tab-btn" data-tab="regional" onclick="switchTab('regional', this)">Regional Report</button>
    <button class="tab-btn" data-tab="rep" onclick="switchTab('rep', this)">Representative Report</button>
</div>

{{-- PRODUCT PANEL --}}
<div class="tab-panel active" id="panel-product">
    <div class="report-card">
        <h3>Top Products by Revenue</h3>
        <div class="legend" style="margin-bottom:10px;">
            @foreach ($products->pluck('category')->unique() as $cat)
                <span><span class="dot" style="background: {{ $categoryColors[$cat] ?? '#94A3B8' }}"></span>{{ $cat }}</span>
            @endforeach
        </div>
        <div style="height:200px;">
            <canvas id="productChart"></canvas>
        </div>
    </div>

    <div class="report-card">
        <h3>Product Performance <span style="font-weight:500; color:var(--sub); font-size:12.5px;">— showing top {{ $products->take(3)->count() }} of {{ $products->count() }}</span></h3>
        <table>
            <tr><th>Product</th><th>Qty Sold</th><th>Actual vs Target</th><th>Status</th></tr>
            @foreach ($products->take(3) as $i => $product)
                <tr>
                    <td><div class="name-cell"><span class="rank-badge">{{ $i + 1 }}</span>{{ $product['name'] }}</div></td>
                    <td>{{ $product['qty'] }}</td>
                    <td>₱{{ number_format($product['actual']) }} / ₱{{ number_format($product['target']) }}</td>
                    <td>
                        @if ($product['actual'] >= $product['target'])
                            <span class="status-pill pill-good">▲ Above target</span>
                        @else
                            <span class="status-pill pill-bad">▼ Below target</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
        <a href="{{ route('reports.sales.products') }}" class="view-more">View full product report →</a>
    </div>

    <div class="report-card">
        <div class="insights-head"><h3>💡 Actionable Insights — Product</h3></div>
        <div class="insight-grid">
            <div class="insight-card warn"><span class="insight-tag">Inventory Warning</span><p class="insight-text">Luzon stock for Wireless Headphones runs out in 6 days at current pace.</p><button class="cta-btn">Reorder Stock</button></div>
            <div class="insight-card warn"><span class="insight-tag">Underperformance</span><p class="insight-text">Phone Case sales are 9% below target this month.</p><button class="cta-btn">Review Pricing</button></div>
            <div class="insight-card good"><span class="insight-tag">Growth Opportunity</span><p class="insight-text">Fast Charger demand is up 34% — consider increasing ad budget.</p><button class="cta-btn">Boost Campaign</button></div>
        </div>
    </div>
</div>

{{-- REGIONAL PANEL --}}
<div class="tab-panel" id="panel-regional">
    <div class="report-card">
        <h3>Regional Sales Comparison</h3>
        <div class="legend" style="margin-bottom:14px;">
            <span><span class="dot" style="background:#14B8A6"></span>Luzon</span>
            <span><span class="dot" style="background:#3B82F6"></span>Visayas</span>
            <span><span class="dot" style="background:#F5B301"></span>Mindanao</span>
        </div>

        <div style="height:220px;">
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
        <h3>Regional Performance</h3>
        <table>
            <tr><th>Region</th><th>Sales</th><th>Target</th><th>Status</th></tr>
            @foreach ($regions as $region)
                @php $percent = $region['percent']; @endphp
                <tr>
                    <td><div class="name-cell"><span class="rank-badge" style="background:{{ $region['color'] }}22; color:{{ $region['color'] }};">{{ substr($region['name'], 0, 1) }}</span>{{ $region['name'] }}</div></td>
                    <td>₱{{ number_format($region['sales']) }}</td>
                    <td>₱{{ number_format($region['target']) }} ({{ $percent }}%)</td>
                    <td>
                        @if ($percent >= 70)
                            <span class="status-pill pill-good">▲ On track</span>
                        @elseif ($percent >= 40)
                            <span class="status-pill pill-bad">▼ Behind</span>
                        @else
                            <span class="status-pill pill-bad">▼ Critical</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
        <a href="{{ route('reports.sales.regional') }}" class="view-more">View full regional report →</a>
    </div>

    <div class="report-card">
        <div class="insights-head"><h3>💡 Actionable Insights — Regional</h3></div>
        <div class="insight-grid">
            <div class="insight-card warn"><span class="insight-tag">Critical Alert</span><p class="insight-text">Mindanao is 23% below target. Recommended: launch a flash sale.</p><button class="cta-btn">Create Campaign</button></div>
            <div class="insight-card info"><span class="insight-tag">Stock Risk</span><p class="insight-text">Luzon is trending toward a stock shortage within 2 weeks.</p><button class="cta-btn">Plan Restock</button></div>
            <div class="insight-card good"><span class="insight-tag">Growth Opportunity</span><p class="insight-text">Visayas conversion rate improved 12% — worth more ad spend.</p><button class="cta-btn">Boost Campaign</button></div>
        </div>
    </div>
</div>

{{-- REP PANEL --}}
<div class="tab-panel" id="panel-rep">
    @php $topRep = $reps->sortByDesc('revenue')->first(); @endphp
    <div class="report-card spotlight-card">
        <div class="spotlight-avatar">{{ $topRep['initials'] }}</div>
        <div class="spotlight-info">
            <span class="spotlight-tag">🔥 Top Performer This Month</span>
            <h3 style="margin:4px 0 2px;">{{ $topRep['name'] }}</h3>
            <div class="legend" style="margin-bottom:0;">
                <span><span class="dot" style="background:{{ $regions->keyBy('name')[$topRep['region']]['color'] }}"></span>{{ $topRep['region'] }}</span>
                <span>{{ $topRep['deals'] }} deals closed</span>
            </div>
        </div>
        <div class="spotlight-stats">
            <div class="spotlight-revenue">₱{{ number_format($topRep['revenue']) }}</div>
            <div class="progress-caption">{{ $topRep['quotaPercent'] }}% of ₱{{ number_format($topRep['quota']) }} quota</div>
        </div>
    </div>

    <div class="report-card">
        <h3>Rep Leaderboard</h3>
        <table>
            <tr><th>Rank</th><th>Representative</th><th>Region</th><th>Revenue</th><th>Quota</th><th>Trend</th></tr>
            @php $medals = ['🥇', '🥈', '🥉']; @endphp
            @foreach ($reps->sortByDesc('revenue')->take(3) as $i => $rep)
                @php $regionColor = $regions->keyBy('name')[$rep['region']]['color']; @endphp
                <tr>
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
            @endforeach
        </table>
        <a href="{{ route('reports.sales.reps') }}" class="view-more">View full rep report →</a>
    </div>

    <div class="report-card">
        <div class="insights-head"><h3>💡 Actionable Insights — Representative</h3></div>
        <div class="insight-grid">
            <div class="insight-card good"><span class="insight-tag">Top Performer</span><p class="insight-text">{{ $topRep['name'] }} exceeded their quota by {{ $topRep['quotaPercent'] - 100 }}% this month.</p><button class="cta-btn">Send Recognition</button></div>
            <div class="insight-card info"><span class="insight-tag">Coaching Opportunity</span><p class="insight-text">One rep's close rate dropped 8% — may need product refresher.</p><button class="cta-btn">Schedule Coaching</button></div>
            <div class="insight-card warn"><span class="insight-tag">Territory Gap</span><p class="insight-text">Luzon relies on a single rep — consider adding backup coverage.</p><button class="cta-btn">Assign Rep</button></div>
        </div>
    </div>
</div>

{{-- KPI DETAIL MODALS --}}
<div class="modal-overlay" id="kpi-modal">
    <div class="modal-box modal-box-wide">
        <button class="modal-x" onclick="closeKpiModal()">✕</button>

        {{-- TOTAL SALES DETAIL --}}
        <div class="kpi-modal-panel" id="kpi-panel-total">
            <h3>Total Sales — Category Breakdown</h3>
            <p class="modal-sub">₱{{ number_format($totalSales['value'], 2) }} this month, +{{ $totalSales['change'] }}% vs last month</p>
            <div class="modal-breakdown-list">
                @foreach ($totalSales['breakdown'] as $b)
                    <div class="modal-breakdown-row">
                        <span class="dot" style="background: {{ $b['color'] }}"></span>
                        <span class="modal-breakdown-name">{{ $b['category'] }}</span>
                        <span class="modal-breakdown-value">₱{{ number_format($b['value']) }}</span>
                        <span class="modal-breakdown-percent">{{ $b['percent'] }}%</span>
                    </div>
                @endforeach
            </div>
            <p class="modal-note"><a href="{{ route('reports.sales.products') }}" style="color:var(--green); font-weight:700;">View full per-product breakdown →</a></p>
        </div>

        {{-- TARGET DETAIL --}}
        <div class="kpi-modal-panel" id="kpi-panel-target" style="display:none;">
            <h3>Target Achievement</h3>
            <p class="modal-sub">You're at {{ $totalSales['percent'] }}% of this month's ₱{{ number_format($totalSales['target'], 2) }} target.</p>
            <div class="modal-stat-box">
                <span class="modal-stat-label">Remaining to reach target</span>
                <span class="modal-stat-value">₱{{ number_format($totalSales['remaining'], 2) }}</span>
            </div>
            <p class="modal-note">At the current daily pace, this target is on track to be met before month end.</p>
        </div>

        {{-- FORECAST DETAIL --}}
        <div class="kpi-modal-panel" id="kpi-panel-forecast" style="display:none;">
            <h3>Sales Forecast — Next Month</h3>
            <p class="modal-sub">Projected at ₱{{ number_format($forecast['value'], 2) }}, +{{ $forecast['change'] }}% vs this month.</p>
            <div class="modal-forecast-range">
                <div class="modal-stat-box">
                    <span class="modal-stat-label">Worst Case</span>
                    <span class="modal-stat-value" style="color:var(--red)">₱{{ number_format($forecast['worst']) }}K</span>
                </div>
                <div class="modal-stat-box">
                    <span class="modal-stat-label">Best Case</span>
                    <span class="modal-stat-value" style="color:var(--green)">₱{{ number_format($forecast['best']) }}K</span>
                </div>
            </div>
            <p class="modal-note">Forecast is projected from recent monthly growth trend. This becomes more accurate once real historical sales data is connected.</p>
        </div>
    </div>
</div>

{{-- EXPORT MODAL --}}
<div class="modal-overlay" id="export-modal">
    <div class="modal-box" style="text-align:left;">
        <h3 style="text-align:center;">Export Report</h3>
        <p style="text-align:center;">Choose what to export and in which format.</p>

        <form action="{{ route('reports.sales.export') }}" method="GET">
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--sub); margin-bottom:6px;">Report</label>
                <select name="report" required
                    style="width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text);">
                    <option value="all">All Reports</option>
                    <option value="product">Product Report</option>
                    <option value="regional">Regional Report</option>
                    <option value="rep">Representative Report</option>
                </select>
            </div>

            <div style="margin-bottom:22px;">
                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--sub); margin-bottom:6px;">File Format</label>
                <select name="format" required
                    style="width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text);">
                    <option value="csv">CSV (.csv)</option>
                    <option value="pdf">PDF (.pdf)</option>
                    <option value="excel">Excel (.xlsx)</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; justify-content:center;">
                <button type="button" class="modal-close" style="background:#fff; color:var(--navy); border:1px solid var(--line);" onclick="closeExportModal()">Cancel</button>
                <button type="submit" class="modal-close">Download</button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPTS (Moved directly into the content section so they are guaranteed to load) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- GLOBAL FUNCTIONS ---
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('panel-' + tab).classList.add('active');
    }
    
    function openExportModal() { document.getElementById('export-modal').classList.add('open'); }
    function closeExportModal() { document.getElementById('export-modal').classList.remove('open'); }

    function openKpiModal(type) {
        document.querySelectorAll('.kpi-modal-panel').forEach(p => p.style.display = 'none');
        document.getElementById('kpi-panel-' + type).style.display = 'block';
        document.getElementById('kpi-modal').classList.add('open');
    }
    function closeKpiModal() { document.getElementById('kpi-modal').classList.remove('open'); }

    const peso = (v) => '₱' + Number(v).toLocaleString('en-PH');

    // --- CHART VARIABLES & FUNCTIONS ---
    let regionalChart;
    let revenueChart;
    const revenueData = @json($revenueChart);

    function buildRevenueDataset(rangeKey) {
        const r = revenueData[rangeKey];
        const labels = [...r.labels, ...r.forecastLabels];
        const actual = [...r.actual, ...r.forecastLabels.map(() => null)];
        const forecast = [...r.actual.map(() => null)];
        forecast[r.actual.length - 1] = r.actual[r.actual.length - 1]; 
        forecast.push(...r.forecastValues);
        const previous = [...r.previous, ...r.forecastLabels.map(() => null)];
        return { labels, actual, forecast, previous, stats: r.stats };
    }

    function renderStats(stats) {
        const bestEl = document.getElementById('stat-best');
        const avgEl = document.getElementById('stat-average');
        const totalEl = document.getElementById('stat-total');
        
        if (bestEl) bestEl.textContent = peso(stats.bestValue) + ' · ' + stats.bestLabel;
        if (avgEl) avgEl.textContent = peso(stats.average);
        if (totalEl) totalEl.textContent = peso(stats.total);
    }

    function initRevenueChart(rangeKey) {
        const d = buildRevenueDataset(rangeKey);
        renderStats(d.stats);

        const canvas = document.getElementById('revenueChart');
        if (!canvas) return; 
        
        const ctx = canvas.getContext('2d');

        if (revenueChart) {
            revenueChart.data.labels = d.labels;
            revenueChart.data.datasets[0].data = d.actual;
            revenueChart.data.datasets[1].data = d.forecast;
            revenueChart.data.datasets[2].data = d.previous;
            revenueChart.update();
            return;
        }

        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [
                    { label: 'Actual', data: d.actual, borderColor: '#1E2A6E', backgroundColor: 'rgba(30,42,110,0.06)', borderWidth: 3, pointRadius: 3, pointHoverRadius: 6, tension: 0.3, fill: true, spanGaps: false },
                    { label: 'Forecast', data: d.forecast, borderColor: '#1FAE6C', borderDash: [6, 6], borderWidth: 3, pointRadius: 3, pointHoverRadius: 6, tension: 0.3, fill: false, spanGaps: true },
                    { label: 'Previous Period', data: d.previous, borderColor: '#B7BCD6', borderDash: [2, 4], borderWidth: 2, pointRadius: 0, tension: 0.3, fill: false, spanGaps: false }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false }, 
                    tooltip: { callbacks: { label: (item) => (item.raw === null) ? null : item.dataset.label + ': ' + peso(item.raw) } }
                },
                scales: {
                    y: { beginAtZero: false, grid: { color: '#E7E9F2' }, ticks: { callback: (v) => '₱' + (v / 1000) + 'K', font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    function setChartRange(range, btn) {
        document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        initRevenueChart(range);
    }

    function setLineEmphasis(activeName) {
        if (!regionalChart) return;
        regionalChart.data.datasets.forEach(ds => ds.borderWidth = (ds.label === activeName) ? 4 : 1.5);
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
        const regionalData = @json($regionalChart);
        Object.keys(regionalData.series).forEach(r => r === name
            ? document.getElementById('region-card-' + r)?.classList.add('region-hover')
            : clearRegionHighlight(r));
        setLineEmphasis(name);
    }

    function unhighlightRegion(name) {
        clearRegionHighlight(name);
        resetLineEmphasis();
    }

    // --- DOM READY EXECUTION ---
    document.addEventListener('DOMContentLoaded', function () {
        
        initRevenueChart('7D');

        const regionalData = @json($regionalChart);
        const regionNames = Object.keys(regionalData.series);

        regionNames.forEach(function (region) {
            const s = regionalData.series[region].stats;
            const bestEl = document.getElementById('stat-best-' + region);
            if (bestEl) bestEl.textContent = peso(s.bestValue) + ' · ' + s.bestLabel;
            const avgEl = document.getElementById('stat-avg-' + region);
            if (avgEl) avgEl.textContent = peso(s.average);
            const totalEl = document.getElementById('stat-total-' + region);
            if (totalEl) totalEl.textContent = peso(s.total);
        });

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
                    responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (item) => item.dataset.label + ': ' + peso(item.raw) } } },
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

        // Added Array fallback in case PHP collection outputs a JSON object
        const productDataRaw = @json($products->take(5));
        const productData = Array.isArray(productDataRaw) ? productDataRaw : Object.values(productDataRaw);
        const productCanvas = document.getElementById('productChart');
        if (productCanvas) {
            new Chart(productCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: productData.map(p => p.name),
                    datasets: [{
                        label: 'Revenue',
                        data: productData.map(p => p.actual),
                        backgroundColor: productData.map(p => p.color),
                        borderRadius: 6,
                        maxBarThickness: 56,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (item) => 'Revenue: ' + peso(item.raw) } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#E7E9F2' }, ticks: { callback: (v) => '₱' + (v / 1000) + 'K', font: { size: 11 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    });
</script>
@endsection