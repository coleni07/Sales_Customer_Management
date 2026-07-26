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

        $orders = SalesOrder::with('items')
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
            ->get();

        $customer = $customerId ? Customer::find($customerId) : null;

        return view('purchase-history.index', compact('orders', 'status', 'dateFrom', 'dateTo', 'customer'));
    }
}