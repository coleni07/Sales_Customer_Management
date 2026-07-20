<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---- Stat cards ----
        $totalSales = SalesOrder::where('status', '!=', 'cancelled')->sum('amount');
        $totalOrders = SalesOrder::count();
        $totalCustomers = Customer::count();

        $lastMonthSales = SalesOrder::where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('amount');
        $thisMonthSales = SalesOrder::where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        $salesGrowth = $lastMonthSales > 0
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
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
        $latestTickets = Ticket::with('customer')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalSales', 'totalOrders', 'totalCustomers',
            'salesGrowth', 'ordersGrowth', 'customersGrowth',
            'salesByDay', 'salesByWeek', 'salesByMonth', 'ordersByStatus', 'ordersByStatusPct',
            'recentOrders', 'latestTickets'
        ));
    }
}
