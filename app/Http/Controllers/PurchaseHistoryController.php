<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $customerId = $request->query('customer_id');

        $orders = SalesOrder::with('items.product')
            ->when($status !== 'all', function ($query) use ($status) {
                if ($status === 'completed') {
                    $query->where('status', 'delivered');
                } elseif ($status === 'cancelled') {
                    $query->where('status', 'cancelled');
                }
            })
            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('order_date', [$dateFrom, $dateTo]);
            })
            ->when($customerId, function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->latest('order_date')
            ->latest('id')
            ->paginate(5)
            ->withQueryString();

        $customer = $customerId ? Customer::find($customerId) : null;

<<<<<<< HEAD
        $summary = [
            'orders_count' => $orders->count(),
            'items_count' => $orders->sum(fn ($order) => $order->items->count()),
            'total_spent' => $orders->sum(fn ($order) => $order->items
                ->where('status', 'delivered')
                ->sum(fn ($item) => $item->price * $item->quantity)),
        ];

        return view('purchase-history.index', [
            'orders' => $orders,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'customer' => $customer,
            'summary' => $summary,
        ]);
=======
        return view('purchase-history.index', compact('orders', 'status', 'dateFrom', 'dateTo', 'customer'));
>>>>>>> 50f2c6f377d6f45668e2d0dcc1b500235ec95d45
    }
}