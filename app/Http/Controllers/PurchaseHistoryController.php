<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        // status: all | completed | cancelled
        $status = $request->query('status', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $customerId = $request->query('customer_id');

        $statusMap = [
            'completed' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        $orders = SalesOrder::with(['items.product'])
            ->when(isset($statusMap[$status]), function ($query) use ($status, $statusMap) {
                $query->where('status', $statusMap[$status]);
            })
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('order_date', [$dateFrom, $dateTo]);
            })
            ->when($customerId, function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->latest('order_date')
            ->get();

        $customer = $customerId ? Customer::find($customerId) : null;

        $summary = [
            'orders_count' => $orders->count(),
            'items_count' => $orders->sum(fn ($order) => $order->items->count()),
            'total_spent' => $orders->where('status', 'delivered')->sum('amount'),
        ];

        return view('purchase-history.index', [
            'orders' => $orders,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'customer' => $customer,
            'summary' => $summary,
        ]);
    }
}
