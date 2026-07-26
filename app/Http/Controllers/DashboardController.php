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
        // Total Sales is calculated the SAME way as the Reports page (month-to-date
        // from the Sale model), so both pages always show the identical figure.
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $daysElapsed = $now->day;

        $totalSales = (float) SalesOrder::where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [$monthStart->format('Y-m-d'), $now->format('Y-m-d')])
            ->sum('amount');

        $totalOrders = SalesOrder::count();
        $totalCustomers = Customer::count();

        // Fair comparison: same number of days into last month, not the whole month
        $lastMonthSamePeriod = (float) SalesOrder::where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [
                $lastMonthStart->format('Y-m-d'),
                $lastMonthStart->copy()->addDays($daysElapsed - 1)->format('Y-m-d'),
            ])->sum('amount');
        $salesGrowth = $lastMonthSamePeriod > 0
            ? round((($totalSales - $lastMonthSamePeriod) / $lastMonthSamePeriod) * 100, 1)
            : 0;

        $thisMonthOrders = SalesOrder::whereBetween('order_date', [$monthStart->format('Y-m-d'), $now->format('Y-m-d')])->count();
        $lastMonthOrdersSamePeriod = SalesOrder::whereBetween('order_date', [
            $lastMonthStart->format('Y-m-d'),
            $lastMonthStart->copy()->addDays($daysElapsed - 1)->format('Y-m-d'),
        ])->count();

        $ordersGrowth = $lastMonthOrdersSamePeriod > 0
            ? round((($thisMonthOrders - $lastMonthOrdersSamePeriod) / $lastMonthOrdersSamePeriod) * 100, 1)
            : 0;

        $thisMonthCustomers = Customer::whereBetween('created_at', [$monthStart, $now])->count();
        $lastMonthCustomersSamePeriod = Customer::whereBetween('created_at', [
            $lastMonthStart,
            $lastMonthStart->copy()->addDays($daysElapsed - 1),
        ])->count();

        $customersGrowth = $lastMonthCustomersSamePeriod > 0
            ? round((($thisMonthCustomers - $lastMonthCustomersSamePeriod) / $lastMonthCustomersSamePeriod) * 100, 1)
            : 0;

        // ---- Sales overview (current week, Mon-Sun) ----
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // 1 Single Query for the entire week
        $weeklyDailySales = SalesOrder::where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->selectRaw('DATE(order_date) as order_date, SUM(amount) as total')
            ->groupBy('order_date')
            ->pluck('total', 'order_date');

        $salesByDay = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $salesByDay[] = [
                'label' => $day->format('D'),
                'total' => (float) ($weeklyDailySales[$dateKey] ?? 0),
            ];
        }

        // ---- Sales overview (this month, grouped by week) ----
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // 1 Single Query for the whole month
        $monthDailySales = SalesOrder::where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->selectRaw('DATE(order_date) as order_date, SUM(amount) as total')
            ->groupBy('order_date')
            ->pluck('total', 'order_date');

        $salesByWeek = [];
        $cursor = $monthStart->copy();
        $weekNum = 1;

        while ($cursor->lte($monthEnd)) {
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY)->min($monthEnd);
            $sum = 0;

            for ($d = $cursor->copy(); $d->lte($weekEnd); $d->addDay()) {
                $sum += (float) ($monthDailySales[$d->format('Y-m-d')] ?? 0);
            }

            $salesByWeek[] = [
                'label' => 'Week ' . $weekNum,
                'total' => $sum,
            ];
            $cursor = $weekEnd->copy()->addDay();
            $weekNum++;
        }

        // ---- Sales overview (this year, grouped by month) ----
        // 1 Single Query for all 12 months
        $yearlySales = SalesOrder::where('status', '!=', 'cancelled')
    ->whereYear('order_date', now()->year)
    ->selectRaw('MONTH(order_date) as month_num, SUM(amount) as total')
    ->groupBy('month_num')
    ->pluck('total', 'month_num');

        $salesByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthDate = Carbon::create(now()->year, $m, 1);
            $salesByMonth[] = [
                'label' => $monthDate->format('M'),
                'total' => (float) ($yearlySales[$m] ?? 0),
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
            fn($v) => round(($v / $totalForStatus) * 100)
        );

        // ---- Tables ----
        $recentOrders = SalesOrder::with(['customer', 'items.product'])->latest('order_date')->latest('id')->take(5)->get();
        $latestTickets = Ticket::with('customer')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalSales',
            'totalOrders',
            'totalCustomers',
            'salesGrowth',
            'ordersGrowth',
            'customersGrowth',
            'salesByDay',
            'salesByWeek',
            'salesByMonth',
            'ordersByStatus',
            'ordersByStatusPct',
            'recentOrders',
            'latestTickets'
        ));
    }
}
