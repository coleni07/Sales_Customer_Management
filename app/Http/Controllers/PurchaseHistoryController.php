<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
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

        $orders = Order::with(['items' => function ($query) use ($status, $statusMap, $dateFrom, $dateTo) {
                if (isset($statusMap[$status])) {
                    $query->where('status', $statusMap[$status]);
                }
                if ($dateFrom && $dateTo) {
                    $query->whereBetween('expected_delivery', [$dateFrom, $dateTo]);
                }
                $query->orderBy('expected_delivery');
            }])
            ->when(isset($statusMap[$status]), function ($query) use ($status, $statusMap) {
                $query->whereHas('items', function ($q) use ($status, $statusMap) {
                    $q->where('status', $statusMap[$status]);
                });
            })
            ->when($customerId, function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->latest()
            ->get()
            ->filter(fn ($order) => $order->items->isNotEmpty())
            ->values();

        $customer = $customerId ? Customer::find($customerId) : null;

        return view('purchase-history.index', [
            'orders' => $orders,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'customer' => $customer,
        ]);
    }
}