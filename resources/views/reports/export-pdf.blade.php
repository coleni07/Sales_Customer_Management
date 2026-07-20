<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; color: #1B1F3B; font-size: 12px; }
        h1 { color: #1E2A6E; font-size: 20px; margin-bottom: 4px; }
        h2 { color: #1E2A6E; font-size: 14px; margin-top: 24px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #E7E9F2; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #F4F5FA; color: #6B7190; }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <p>Generated: {{ now()->format('F j, Y') }}</p>

    @if ($report === 'product' || $report === 'all')
        <h2>Product Report</h2>
        <table>
            <tr><th>Product</th><th>Qty Sold</th><th>Actual</th><th>Target</th><th>Status</th></tr>
            @foreach ($products as $p)
                <tr>
                    <td>{{ $p['name'] }}</td>
                    <td>{{ $p['qty'] }}</td>
                    <td>₱{{ number_format($p['actual']) }}</td>
                    <td>₱{{ number_format($p['target']) }}</td>
                    <td>{{ $p['actual'] >= $p['target'] ? 'Above Target' : 'Below Target' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($report === 'regional' || $report === 'all')
        <h2>Regional Report</h2>
        <table>
            <tr><th>Region</th><th>Sales</th><th>Target</th><th>Percent</th></tr>
            @foreach ($regions as $r)
                <tr>
                    <td>{{ $r['name'] }}</td>
                    <td>₱{{ number_format($r['sales']) }}</td>
                    <td>₱{{ number_format($r['target']) }}</td>
                    <td>{{ round(($r['sales'] / $r['target']) * 100) }}%</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($report === 'rep' || $report === 'all')
        <h2>Representative Report</h2>
        <table>
            <tr><th>Representative</th><th>Revenue</th><th>Deals Closed</th></tr>
            @foreach ($reps as $r)
                <tr>
                    <td>{{ $r['name'] }}</td>
                    <td>₱{{ number_format($r['revenue']) }}</td>
                    <td>{{ $r['deals'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
