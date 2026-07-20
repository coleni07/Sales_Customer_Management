@extends('layouts.report-detail')

@section('detail-content')

<div class="stats-strip" style="margin-bottom:20px; border-bottom:none; padding-bottom:0;">
    <div class="stat-block"><span class="stat-label">Total Products</span><span class="stat-value">{{ $stats['totalProducts'] }}</span></div>
    <div class="stat-block"><span class="stat-label">Best-Selling Category</span><span class="stat-value">{{ $stats['bestCategory'] }}</span></div>
    <div class="stat-block"><span class="stat-label">Total Revenue</span><span class="stat-value">₱{{ number_format($stats['totalRevenue']) }}</span></div>
</div>

<div class="report-card">
    <div class="filter-row">
        <input type="text" id="product-search" class="search-input" placeholder="Search products..." onkeyup="filterProducts()">
        <div class="chip-row" id="category-chips">
            <button class="chip active" data-category="all" onclick="setCategoryFilter('all', this)">All</button>
            @foreach ($categoryColors as $cat => $color)
                <button class="chip" data-category="{{ $cat }}" onclick="setCategoryFilter('{{ $cat }}', this)" style="--chip-color: {{ $color }}">{{ $cat }}</button>
            @endforeach
        </div>
    </div>

    <table>
        <tr>
            <th></th>
            <th class="sortable" onclick="sortTable('name')">Product</th>
            <th class="sortable" onclick="sortTable('categoryRaw')">Category</th>
            <th class="sortable" onclick="sortTable('qty')">Qty Sold</th>
            <th class="sortable" onclick="sortTable('actual')">Revenue</th>
            <th>Status</th>
        </tr>
        <tbody id="product-table-body">
        @foreach ($products as $i => $product)
            <tr class="product-row" data-category-raw="{{ $product['category'] }}" data-name="{{ strtolower($product['name']) }}"
                data-qty="{{ $product['qty'] }}" data-actual="{{ $product['actual'] }}"
                onclick="toggleExpand('product-{{ $i }}')">
                <td><span class="expand-caret" id="caret-product-{{ $i }}">▸</span></td>
                <td><div class="name-cell">{{ $product['name'] }}</div></td>
                <td><span class="dot" style="background:{{ $product['color'] }}"></span> {{ $product['category'] }}</td>
                <td>{{ $product['qty'] }}</td>
                <td>₱{{ number_format($product['actual']) }}</td>
                <td>
                    @if ($product['actual'] >= $product['target'])
                        <span class="status-pill pill-good">▲ Above target</span>
                    @else
                        <span class="status-pill pill-bad">▼ Below target</span>
                    @endif
                </td>
            </tr>
            <tr class="expand-row" id="product-{{ $i }}" style="display:none;">
                <td colspan="6">
                    <div class="expand-panel">
                        <div class="expand-col">
                            <span class="expand-label">6-Month Trend</span>
                            @php
                                $trend = $product['trend'];
                                $max = max($trend) ?: 1;
                                $pts = collect($trend)->map(function ($v, $idx) use ($trend, $max) {
                                    $x = $idx * (200 / (count($trend) - 1));
                                    $y = 45 - ($v / $max * 40);
                                    return "$x,$y";
                                })->implode(' ');
                            @endphp
                            <svg viewBox="0 0 200 50" class="mini-sparkline" preserveAspectRatio="none">
                                <polyline points="{{ $pts }}" fill="none" stroke="{{ $product['color'] }}" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="expand-col">
                            <span class="expand-label">Regional Split</span>
                            @foreach ($product['regionSplit'] as $regionName => $pct)
                                @php $rc = $regions->keyBy('name')[$regionName]['color']; @endphp
                                <div class="mini-bar-row">
                                    <span class="mini-bar-label">{{ $regionName }}</span>
                                    <div class="mini-bar-track"><div class="mini-bar-fill" style="width:{{ $pct }}%; background:{{ $rc }};"></div></div>
                                    <span class="mini-bar-pct">{{ $pct }}%</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="expand-col">
                            <span class="expand-label">Top Reps Selling This</span>
                            @foreach ($product['topReps'] as $repName)
                                <div class="expand-tag">{{ $repName }}</div>
                            @endforeach
                        </div>
                        <div class="expand-col">
                            <span class="expand-label">Stock Level</span>
                            <div class="mini-bar-track">
                                <div class="mini-bar-fill" style="width:{{ $product['stockLevel'] }}%; background:{{ $product['stockLevel'] < 30 ? 'var(--red)' : ($product['stockLevel'] < 60 ? 'var(--gold)' : 'var(--green)') }};"></div>
                            </div>
                            <span class="mini-bar-pct">{{ $product['stockLevel'] }}%</span>
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

    window.activeCategory = 'all';

    function filterProducts() {
        const term = document.getElementById('product-search').value.toLowerCase();
        document.querySelectorAll('.product-row').forEach(row => {
            const matchesSearch = row.dataset.name.includes(term);
            const matchesCategory = window.activeCategory === 'all' || row.dataset.categoryRaw === window.activeCategory;
            const show = matchesSearch && matchesCategory;
            row.style.display = show ? '' : 'none';
            const expandRow = row.nextElementSibling;
            if (!show && expandRow) expandRow.style.display = 'none';
        });
    }

    function setCategoryFilter(category, btn) {
        window.activeCategory = category;
        document.querySelectorAll('#category-chips .chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        filterProducts();
    }

    window.sortDir = 'asc';
    function sortTable(key) {
        const tbody = document.getElementById('product-table-body');
        const rows = Array.from(tbody.querySelectorAll('.product-row'));
        window.sortDir = window.sortDir === 'asc' ? 'desc' : 'asc';
        const dir = window.sortDir === 'asc' ? 1 : -1;

        rows.sort((a, b) => {
            let av = a.dataset[key];
            let bv = b.dataset[key];
            const an = Number(av), bn = Number(bv);
            if (!isNaN(an) && !isNaN(bn) && av !== '' && bv !== '') { av = an; bv = bn; }
            return av > bv ? dir : av < bv ? -dir : 0;
        });

        rows.forEach(row => {
            const expandRow = row.nextElementSibling;
            tbody.appendChild(row);
            if (expandRow) tbody.appendChild(expandRow);
        });
    }
</script>
@endpush
