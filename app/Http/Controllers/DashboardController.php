<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalesOrder;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---- Stat cards ----
        // Total Sales is calculated the SAME way as the Reports page (month-to-date
        // from the Sale model), so both pages always show the identical figure.
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $daysElapsed = $now->day;

        $totalSales = (float) Sale::whereBetween('sale_date', [$monthStart->format('Y-m-d'), $now->format('Y-m-d')])->sum('amount');
        $totalOrders = SalesOrder::count();
        $totalCustomers = Customer::count();

        // Fair comparison: same number of days into last month, not the whole month
        $lastMonthSamePeriod = (float) Sale::whereBetween('sale_date', [
            $lastMonthStart->format('Y-m-d'),
            $lastMonthStart->copy()->addDays($daysElapsed - 1)->format('Y-m-d'),
        ])->sum('amount');
        $salesGrowth = $lastMonthSamePeriod > 0
            ? round((($totalSales - $lastMonthSamePeriod) / $lastMonthSamePeriod) * 100, 1)
            : 0;

        $ordersGrowth = 10.5;   // placeholder KPIs, swap for real period-over-period calc as needed
        $customersGrowth = 6.7;

        // ---- Sales overview (current week, Mon-Sun) ----
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $salesByDay = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $salesByDay[] = [
                'label' => $day->format('D'),
                'total' => (float) SalesOrder::where('status', '!=', 'cancelled')
                    ->whereDate('order_date', $day)
                    ->sum('amount'),
            ];
        }

        // ---- Sales overview (this month, grouped by week) ----
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $salesByWeek = [];
        $cursor = $monthStart->copy();
        $weekNum = 1;
        while ($cursor->lte($monthEnd)) {
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY)->min($monthEnd);
            $salesByWeek[] = [
                'label' => 'Week ' . $weekNum,
                'total' => (float) SalesOrder::where('status', '!=', 'cancelled')
                    ->whereBetween('order_date', [$cursor, $weekEnd])
                    ->sum('amount'),
            ];
            $cursor = $weekEnd->copy()->addDay();
            $weekNum++;
        }

        // ---- Sales overview (this year, grouped by month) ----
        $salesByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthDate = Carbon::create(now()->year, $m, 1);
            $salesByMonth[] = [
                'label' => $monthDate->format('M'),
                'total' => (float) SalesOrder::where('status', '!=', 'cancelled')
                    ->whereYear('order_date', $monthDate->year)
                    ->whereMonth('order_date', $monthDate->month)
                    ->sum('amount'),
            ];
        }

        // ---- Orders by status (donut) ----
        $statusCounts = SalesOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalForStatus = max($statusCounts->sum(), 1);
        $ordersByStatus = [
            'pending' => $statusCounts->get('pending', 0),
            'processing' => $statusCounts->get('processing', 0),
            'shipped' => $statusCounts->get('shipped', 0),
            'delivered' => $statusCounts->get('delivered', 0),
            'cancelled' => $statusCounts->get('cancelled', 0),
        ];
        $ordersByStatusPct = collect($ordersByStatus)->map(
            fn ($v) => round(($v / $totalForStatus) * 100)
        );

        // ---- Tables ----
        $recentOrders = SalesOrder::with('customer')->latest('order_date')->latest('id')->take(5)->get();
        $latestTickets = SupportTicket::latest()->take(5)->get();

        return view('dashboard', compact(
            'totalSales', 'totalOrders', 'totalCustomers',
            'salesGrowth', 'ordersGrowth', 'customersGrowth',
            'salesByDay', 'salesByWeek', 'salesByMonth', 'ordersByStatus', 'ordersByStatusPct',
            'recentOrders', 'latestTickets'
        ));
    }
}
