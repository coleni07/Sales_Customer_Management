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

        $orders = SalesOrder::with(['items.product', 'customer'])
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

        if ($request->wantsJson()) {
            return response()->json([
                'customer' => $customer,
                'summary' => $summary,
                'orders' => $orders->map(function ($order) {
                    return [
                        'order_no' => $order->order_no,
                        'order_date' => $order->order_date->format('jS F Y'),
                        'status' => $order->status,
                        'amount' => $order->amount,
                        'customer_name' => optional($order->customer)->name,
                        'customer_code' => optional($order->customer)->customer_code,
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_name' => $item->product->name ?? 'Unknown Product',
                                'category' => $item->product->category ?? '',
                                'qty' => $item->qty,
                                'price' => $item->price,
                            ];
                        }),
                    ];
                }),
            ]);
        }

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
