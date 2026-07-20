<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Region;
use App\Models\Representative;
use App\Models\Sale;

class SalesReportController extends Controller
{
    /**
     * Sums sales per calendar day within a range in ONE query, returned as
     * ['Y-m-d' => total]. Used as the raw material for day/week/month bucketing
     * below, so we only hit the database once per range instead of once per bucket.
     */
    private function dailyTotals(Carbon $start, Carbon $end): array
    {
        return Sale::whereBetween('sale_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->selectRaw('sale_date, SUM(amount) as total')
            ->groupBy('sale_date')
            ->pluck('total', 'sale_date')
            ->mapWithKeys(fn ($v, $k) => [Carbon::parse($k)->format('Y-m-d') => (float) $v])
            ->all();
    }

    /**
     * Groups a daily-totals array (from dailyTotals()) into day/week/month buckets,
     * filling gaps with 0 so the chart never has a missing data point.
     */
    private function bucketTotals(array $daily, Carbon $start, Carbon $end, string $unit, string $labelFormat): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy();

        if ($unit === 'day') {
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format($labelFormat);
                $values[] = round($daily[$cursor->format('Y-m-d')] ?? 0);
                $cursor->addDay();
            }
        } elseif ($unit === 'week') {
            while ($cursor->lte($end)) {
                $bucketEnd = $cursor->copy()->addDays(6)->min($end);
                $sum = 0;
                for ($d = $cursor->copy(); $d->lte($bucketEnd); $d->addDay()) {
                    $sum += $daily[$d->format('Y-m-d')] ?? 0;
                }
                $labels[] = $cursor->format($labelFormat);
                $values[] = round($sum);
                $cursor->addDays(7);
            }
        } else { // month
            while ($cursor->lte($end)) {
                $bucketEnd = $cursor->copy()->endOfMonth()->min($end);
                $sum = 0;
                for ($d = $cursor->copy(); $d->lte($bucketEnd); $d->addDay()) {
                    $sum += $daily[$d->format('Y-m-d')] ?? 0;
                }
                $labels[] = $cursor->format($labelFormat);
                $values[] = round($sum);
                $cursor = $cursor->copy()->addMonthNoOverflow()->startOfMonth();
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Average month-over-month growth rate from the last 3 complete months of sales.
     * A simple trend projection — good enough for a dashboard forecast, not a
     * substitute for real statistical forecasting.
     */
    private function monthlyGrowthRate(): float
    {
        $now = Carbon::now();
        $totals = [];
        for ($i = 3; $i >= 1; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end = $now->copy()->subMonths($i)->endOfMonth();
            $totals[] = (float) Sale::whereBetween('sale_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');
        }

        $rates = [];
        for ($i = 1; $i < count($totals); $i++) {
            if ($totals[$i - 1] > 0) {
                $rates[] = ($totals[$i] - $totals[$i - 1]) / $totals[$i - 1];
            }
        }

        return $rates ? array_sum($rates) / count($rates) : 0.05; // fallback: assume 5% if not enough history
    }

    /**
     * Real Revenue Overview chart data for all 4 time ranges (7D/30D/3M/6M),
     * each with its own actual/previous-period/forecast series — queried from
     * the sales table instead of generated.
     */
    private function buildRevenueChart(): array
    {
        $growthRate = $this->monthlyGrowthRate();
        $now = Carbon::now();

        $configs = [
            '7D'  => ['days' => 7,   'unit' => 'day',   'labelFmt' => 'M j', 'stepGrowth' => $growthRate / 30],
            '30D' => ['days' => 30,  'unit' => 'day',   'labelFmt' => 'M j', 'stepGrowth' => $growthRate / 30],
            '3M'  => ['days' => 90,  'unit' => 'week',  'labelFmt' => 'M j', 'stepGrowth' => $growthRate / 4],
            '6M'  => ['days' => 180, 'unit' => 'month', 'labelFmt' => 'M',   'stepGrowth' => $growthRate],
        ];

        $ranges = [];

        foreach ($configs as $key => $cfg) {
            $end = $now->copy();
            $start = $now->copy()->subDays($cfg['days'] - 1);
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($cfg['days'] - 1);

            $current = $this->bucketTotals($this->dailyTotals($start, $end), $start, $end, $cfg['unit'], $cfg['labelFmt']);
            $previous = $this->bucketTotals($this->dailyTotals($prevStart, $prevEnd), $prevStart, $prevEnd, $cfg['unit'], $cfg['labelFmt']);

            $actual = $current['values'];
            $labels = $current['labels'];
            $previousValues = array_slice(array_pad($previous['values'], count($actual), 0), 0, count($actual));

            // Forecast tail: 2 points continuing from the last actual value
            $forecastLabels = [];
            $forecastValues = [end($actual) ?: 0];
            $stepDate = $end->copy();
            for ($i = 1; $i <= 2; $i++) {
                $stepDate = match ($cfg['unit']) {
                    'day'   => $stepDate->copy()->addDay(),
                    'week'  => $stepDate->copy()->addWeek(),
                    'month' => $stepDate->copy()->addMonthNoOverflow(),
                };
                $forecastLabels[] = $stepDate->format($cfg['labelFmt']);
                $forecastValues[] = round(end($forecastValues) * (1 + $cfg['stepGrowth']));
            }

            $bestIndex = $actual ? array_keys($actual, max($actual))[0] : 0;

            $ranges[$key] = [
                'labels'         => $labels,
                'actual'         => $actual,
                'previous'       => $previousValues,
                'forecastLabels' => $forecastLabels,
                'forecastValues' => array_slice($forecastValues, 1),
                'stats'          => [
                    'bestLabel' => $labels[$bestIndex] ?? '—',
                    'bestValue' => $actual[$bestIndex] ?? 0,
                    'average'   => count($actual) ? round(array_sum($actual) / count($actual)) : 0,
                    'total'     => array_sum($actual),
                ],
            ];
        }

        return $ranges;
    }

    /**
     * Central place for the report data.
     * Later, swap this out for real queries/models — nothing else needs to change.
     */
    /**
     * Category colors — reused across the Total Sales breakdown AND the Product
     * report's bar chart, so category = color means the same thing everywhere on the page.
     */
    private function categoryColors(): array
    {
        return [
            'Audio'       => '#14B8A6', // teal
            'Power'       => '#3B82F6', // blue
            'Accessories' => '#F5B301', // gold
        ];
    }

    /**
     * Real 14-day per-region trend for the Regional Sales Comparison chart.
     */
    private function buildRegionalDailyTrend(): array
    {
        $now = Carbon::now();
        $days = 14;
        $start = $now->copy()->subDays($days - 1);

        $labels = [];
        for ($c = $start->copy(); $c->lte($now); $c->addDay()) {
            $labels[] = $c->format('M j');
        }

        $series = [];
        foreach (Region::all() as $region) {
            $daily = Sale::where('region_id', $region->id)
                ->whereBetween('sale_date', [$start->format('Y-m-d'), $now->format('Y-m-d')])
                ->selectRaw('sale_date, SUM(amount) as total')
                ->groupBy('sale_date')
                ->pluck('total', 'sale_date')
                ->mapWithKeys(fn ($v, $k) => [Carbon::parse($k)->format('Y-m-d') => (float) $v])
                ->all();

            $values = [];
            for ($c = $start->copy(); $c->lte($now); $c->addDay()) {
                $values[] = round($daily[$c->format('Y-m-d')] ?? 0);
            }

            $bestIndex = $values ? array_keys($values, max($values))[0] : 0;

            $series[$region->name] = [
                'data'  => $values,
                'color' => $region->color,
                'stats' => [
                    'bestLabel' => $labels[$bestIndex] ?? '—',
                    'bestValue' => $values[$bestIndex] ?? 0,
                    'average'   => count($values) ? round(array_sum($values) / count($values)) : 0,
                    'total'     => array_sum($values),
                ],
            ];
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function getData(): array
    {
        $categoryColors = $this->categoryColors();
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $daysElapsed = $now->day;

        // ===== Total Sales (current month-to-date) =====
        $totalValue = (float) Sale::whereBetween('sale_date', [$monthStart->format('Y-m-d'), $now->format('Y-m-d')])->sum('amount');
        $totalTarget = (float) Region::sum('monthly_target');

        // Fair comparison: same number of days into last month, not the whole month
        $lastMonthSamePeriod = (float) Sale::whereBetween('sale_date', [
            $lastMonthStart->format('Y-m-d'),
            $lastMonthStart->copy()->addDays($daysElapsed - 1)->format('Y-m-d'),
        ])->sum('amount');
        $change = $lastMonthSamePeriod > 0 ? round((($totalValue - $lastMonthSamePeriod) / $lastMonthSamePeriod) * 100, 1) : 0;
        $percent = $totalTarget > 0 ? round(($totalValue / $totalTarget) * 100) : 0;

        // ===== Category breakdown (Total Sales card) =====
        $breakdownRaw = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sale_date', [$monthStart->format('Y-m-d'), $now->format('Y-m-d')])
            ->selectRaw('products.category as category, SUM(sales.amount) as total')
            ->groupBy('products.category')
            ->pluck('total', 'category');
        $breakdownSum = $breakdownRaw->sum();

        $breakdown = $breakdownRaw->map(function ($value, $category) use ($breakdownSum, $categoryColors) {
            return [
                'category' => $category,
                'value'    => round($value),
                'percent'  => $breakdownSum > 0 ? round(($value / $breakdownSum) * 100) : 0,
                'color'    => $categoryColors[$category] ?? '#94A3B8',
            ];
        })->sortByDesc('value')->values();

        // ===== Forecast (simple trend projection) =====
        $growthRate = $this->monthlyGrowthRate();
        $lastCompleteMonthTotal = (float) Sale::whereBetween('sale_date', [$lastMonthStart->format('Y-m-d'), $lastMonthEnd->format('Y-m-d')])->sum('amount');
        $forecastValue = round($lastCompleteMonthTotal * (1 + $growthRate));

        // ===== Products =====
        $monthFilter = fn ($q) => $q->whereBetween('sale_date', [$monthStart->format('Y-m-d'), $now->format('Y-m-d')]);

        $products = Product::withSum(['sales as actual' => $monthFilter], 'amount')
            ->withSum(['sales as qty' => $monthFilter], 'quantity')
            ->get()
            ->map(function ($p) use ($categoryColors) {
                return [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'category' => $p->category,
                    'qty'      => (int) ($p->qty ?? 0),
                    'actual'   => round($p->actual ?? 0),
                    'target'   => (float) $p->monthly_target,
                    'color'    => $categoryColors[$p->category] ?? '#94A3B8',
                ];
            })
            ->sortByDesc('actual')
            ->values();

        // ===== Regions =====
        $regions = Region::withSum(['sales as sales' => $monthFilter], 'amount')
            ->get()
            ->map(function ($r) {
                $sales = round($r->sales ?? 0);
                return [
                    'id'      => $r->id,
                    'name'    => $r->name,
                    'sales'   => $sales,
                    'target'  => (float) $r->monthly_target,
                    'color'   => $r->color,
                    'percent' => $r->monthly_target > 0 ? round(($sales / $r->monthly_target) * 100) : 0,
                ];
            });

        // ===== Representatives =====
        $lastMonthFilter = fn ($q) => $q->whereBetween('sale_date', [$lastMonthStart->format('Y-m-d'), $lastMonthEnd->format('Y-m-d')]);

        $reps = Representative::with('region')
            ->withSum(['sales as revenue' => $monthFilter], 'amount')
            ->withCount(['sales as deals' => $monthFilter])
            ->withSum(['sales as lastMonthRevenue' => $lastMonthFilter], 'amount')
            ->get()
            ->map(function ($rep) {
                $revenue = round($rep->revenue ?? 0);
                $lastMonthRevenue = round($rep->lastMonthRevenue ?? 0);
                $repChange = $lastMonthRevenue > 0 ? round((($revenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

                return [
                    'id'           => $rep->id,
                    'name'         => $rep->name,
                    'revenue'      => $revenue,
                    'deals'        => (int) ($rep->deals ?? 0),
                    'region'       => $rep->region->name,
                    'quota'        => (float) $rep->monthly_quota,
                    'change'       => $repChange,
                    'quotaPercent' => $rep->monthly_quota > 0 ? round(($revenue / $rep->monthly_quota) * 100) : 0,
                    'initials'     => collect(explode(' ', $rep->name))->map(fn ($w) => strtoupper($w[0]))->implode(''),
                ];
            });

        return [
            'totalSales' => [
                'value'     => $totalValue,
                'target'    => $totalTarget,
                'percent'   => $percent,
                'change'    => $change,
                'remaining' => max($totalTarget - $totalValue, 0),
                'breakdown' => $breakdown,
            ],
            'forecast' => [
                'value'  => $forecastValue,
                'change' => round($growthRate * 100, 1),
                'best'   => round($forecastValue * 1.10 / 1000),
                'worst'  => round($forecastValue * 0.90 / 1000),
            ],
            'revenueChart'    => $this->buildRevenueChart(),
            'products'        => $products,
            'categoryColors'  => $categoryColors,
            'regions'         => $regions,
            'regionalChart'   => $this->buildRegionalDailyTrend(),
            'reps'            => $reps,
        ];
    }

    public function index()
    {
        return view('reports.sales', $this->getData());
    }

    /**
     * Still used for stock level only — there's no real inventory table yet,
     * so this stays simulated (seeded on name so it's stable across reloads).
     */
    private function seededInt(string $seed, int $min, int $max): int
    {
        mt_srand(crc32($seed));
        return mt_rand($min, $max);
    }

    /**
     * These query helpers answer the "how does X relate to Y" cross-report questions
     * (a product's regional split, a rep's product mix, etc.) directly from the sales
     * table, now that real relational data exists. They use ALL sales history (not just
     * month-to-date) since these are meant to show overall patterns/profile, not a
     * single month's snapshot — with only a few days into the current month, month-to-date
     * cross-breakdowns would be too sparse to be meaningful.
     */
    private function productRegionSplit(int $productId, Collection $regions): array
    {
        $totals = Sale::where('product_id', $productId)
            ->selectRaw('region_id, SUM(amount) as total')
            ->groupBy('region_id')
            ->pluck('total', 'region_id');
        $sum = $totals->sum();

        $result = [];
        foreach ($regions as $region) {
            $val = $totals[$region['id']] ?? 0;
            $result[$region['name']] = $sum > 0 ? round(($val / $sum) * 100) : 0;
        }
        return $result;
    }

    private function topRepsForProduct(int $productId, int $limit = 2): array
    {
        return Sale::where('product_id', $productId)
            ->join('representatives', 'sales.representative_id', '=', 'representatives.id')
            ->selectRaw('representatives.name as name, SUM(sales.amount) as total')
            ->groupBy('representatives.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('name')
            ->all();
    }

    private function productMonthlyTrend(int $productId, int $months = 6): array
    {
        $now = Carbon::now();
        $values = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end = $now->copy()->subMonths($i)->endOfMonth();
            $values[] = round(Sale::where('product_id', $productId)
                ->whereBetween('sale_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->sum('amount'));
        }
        return $values;
    }

    private function topProductsInRegion(int $regionId, int $limit = 3): array
    {
        return Sale::where('region_id', $regionId)
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->selectRaw('products.name as name, SUM(sales.amount) as value')
            ->groupBy('products.name')
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'value' => round($row->value)])
            ->all();
    }

    private function productMixForRep(int $repId, int $limit = 3): array
    {
        $repTotal = Sale::where('representative_id', $repId)->sum('amount');

        return Sale::where('representative_id', $repId)
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->selectRaw('products.name as name, SUM(sales.amount) as total')
            ->groupBy('products.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'percent' => $repTotal > 0 ? round(($row->total / $repTotal) * 100) : 0])
            ->all();
    }

    private function coverageForRep(int $repId, Collection $regions): array
    {
        $totals = Sale::where('representative_id', $repId)
            ->selectRaw('region_id, SUM(amount) as total')
            ->groupBy('region_id')
            ->pluck('total', 'region_id');
        $sum = $totals->sum();

        $result = [];
        foreach ($regions as $region) {
            $val = $totals[$region['id']] ?? 0;
            $result[$region['name']] = $sum > 0 ? round(($val / $sum) * 100) : 0;
        }
        return $result;
    }

    private function repMonthlyTrend(int $repId, int $months = 6): array
    {
        $now = Carbon::now();
        $values = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end = $now->copy()->subMonths($i)->endOfMonth();
            $values[] = round(Sale::where('representative_id', $repId)
                ->whereBetween('sale_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->sum('amount'));
        }
        return $values;
    }

    /**
     * GET /reports/sales/products — dedicated Product Report page
     */
    public function productDetail()
    {
        $data = $this->getData();

        $data['products'] = $data['products']->map(function ($p) use ($data) {
            $p['regionSplit'] = $this->productRegionSplit($p['id'], $data['regions']);
            $p['topReps'] = $this->topRepsForProduct($p['id']);

            // No inventory table exists yet, so stock level is still simulated —
            // would need a real `inventory` or `stock` table to make this genuine.
            $p['stockLevel'] = $this->seededInt($p['name'] . '-stock', 12, 96);

            $p['trend'] = $this->productMonthlyTrend($p['id']);
            return $p;
        });

        $data['pageTitle'] = 'Product Report';
        $data['reportKey'] = 'product';
        $data['stats'] = [
            'totalProducts' => $data['products']->count(),
            'bestCategory'  => $data['products']->groupBy('category')->map->sum('actual')->sortDesc()->keys()->first(),
            'totalRevenue'  => $data['products']->sum('actual'),
        ];

        return view('reports.product-detail', $data);
    }

    /**
     * GET /reports/sales/regional — dedicated Regional Report page
     */
    public function regionalDetail()
    {
        $data = $this->getData();

        $data['regions'] = $data['regions']->map(function ($r) use ($data) {
            $r['topProducts'] = collect($this->topProductsInRegion($r['id']));
            $r['reps'] = $data['reps']->where('region', $r['name'])->values();
            return $r;
        });

        $data['pageTitle'] = 'Regional Report';
        $data['reportKey'] = 'regional';

        return view('reports.regional-detail', $data);
    }

    /**
     * GET /reports/sales/representatives — dedicated Representative Report page
     */
    public function repDetail()
    {
        $data = $this->getData();

        $data['reps'] = $data['reps']->map(function ($rep) use ($data) {
            $rep['productMix'] = collect($this->productMixForRep($rep['id']));
            $rep['coverage'] = $this->coverageForRep($rep['id'], $data['regions']);
            $rep['quotaTrend'] = $this->repMonthlyTrend($rep['id']);
            return $rep;
        });

        $data['pageTitle'] = 'Representative Report';
        $data['reportKey'] = 'rep';

        return view('reports.rep-detail', $data);
    }

    /**
     * GET /reports/sales/export?format=csv&report=product
     * format: csv | pdf | excel
     * report: product | regional | rep | all
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf,excel',
            'report' => 'required|in:product,regional,rep,all',
        ]);

        $format = $request->query('format');
        $report = $request->query('report');
        $data   = $this->getData();

        return match ($format) {
            'csv'   => $this->exportCsv($report, $data),
            'pdf'   => $this->exportPdf($report, $data),
            'excel' => $this->exportExcel($report, $data),
        };
    }

    private function exportCsv(string $report, array $data)
    {
        $filename = "sales-report-{$report}.csv";

        return response()->streamDownload(function () use ($report, $data) {
            $handle = fopen('php://output', 'w');

            if ($report === 'product' || $report === 'all') {
                fputcsv($handle, ['Product Report']);
                fputcsv($handle, ['Product', 'Qty Sold', 'Actual', 'Target', 'Status']);
                foreach ($data['products'] as $p) {
                    $status = $p['actual'] >= $p['target'] ? 'Above Target' : 'Below Target';
                    fputcsv($handle, [$p['name'], $p['qty'], $p['actual'], $p['target'], $status]);
                }
                fputcsv($handle, []);
            }

            if ($report === 'regional' || $report === 'all') {
                fputcsv($handle, ['Regional Report']);
                fputcsv($handle, ['Region', 'Sales', 'Target', 'Percent']);
                foreach ($data['regions'] as $r) {
                    $percent = round(($r['sales'] / $r['target']) * 100);
                    fputcsv($handle, [$r['name'], $r['sales'], $r['target'], $percent . '%']);
                }
                fputcsv($handle, []);
            }

            if ($report === 'rep' || $report === 'all') {
                fputcsv($handle, ['Representative Report']);
                fputcsv($handle, ['Representative', 'Revenue', 'Deals Closed']);
                foreach ($data['reps'] as $r) {
                    fputcsv($handle, [$r['name'], $r['revenue'], $r['deals']]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportPdf(string $report, array $data)
    {
        $pdf = Pdf::loadView('reports.export-pdf', [
            'report' => $report,
            ...$data,
        ]);

        return $pdf->download("sales-report-{$report}.pdf");
    }

    private function exportExcel(string $report, array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        if ($report === 'product' || $report === 'all') {
            $sheet->setCellValue("A{$row}", 'Product Report');
            $row++;
            $sheet->fromArray(['Product', 'Qty Sold', 'Actual', 'Target', 'Status'], null, "A{$row}");
            $row++;
            foreach ($data['products'] as $p) {
                $status = $p['actual'] >= $p['target'] ? 'Above Target' : 'Below Target';
                $sheet->fromArray([$p['name'], $p['qty'], $p['actual'], $p['target'], $status], null, "A{$row}");
                $row++;
            }
            $row++;
        }

        if ($report === 'regional' || $report === 'all') {
            $sheet->setCellValue("A{$row}", 'Regional Report');
            $row++;
            $sheet->fromArray(['Region', 'Sales', 'Target', 'Percent'], null, "A{$row}");
            $row++;
            foreach ($data['regions'] as $r) {
                $percent = round(($r['sales'] / $r['target']) * 100) . '%';
                $sheet->fromArray([$r['name'], $r['sales'], $r['target'], $percent], null, "A{$row}");
                $row++;
            }
            $row++;
        }

        if ($report === 'rep' || $report === 'all') {
            $sheet->setCellValue("A{$row}", 'Representative Report');
            $row++;
            $sheet->fromArray(['Representative', 'Revenue', 'Deals Closed'], null, "A{$row}");
            $row++;
            foreach ($data['reps'] as $r) {
                $sheet->fromArray([$r['name'], $r['revenue'], $r['deals']], null, "A{$row}");
                $row++;
            }
        }

        $filename = "sales-report-{$report}.xlsx";
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
