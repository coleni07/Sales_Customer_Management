<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $initialSearch = (string) $request->query('search', '');
        $initialSort = (string) $request->query('sort', 'default');

        $customers = Customer::query()
            ->withCount('orders')
            ->with(['orders' => function ($query) {
                $query->latest('order_date')->limit(1);
            }])
            ->orderBy('name')
            ->get();

        $customersData = $customers->map(function (Customer $customer) {
            $lastOrder = $customer->orders->first();
            $initials = collect(explode(' ', trim($customer->name)))
                ->map(fn ($word) => mb_substr($word, 0, 1))
                ->take(2)
                ->join('');

            $avatarPalette = [
                'bg-brand/15 text-brand-dark',
                'bg-navy/10 text-navy',
                'bg-amber-100 text-amber-700',
                'bg-rose-100 text-rose-700',
                'bg-sky-100 text-sky-700',
            ];

            return [
                'id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'customerCode' => $customer->customer_code,
                'name' => $customer->name,
                'location' => $customer->location,
                'masked_phone' => $customer->masked_phone,
                'maskedPhone' => $customer->masked_phone,
                'total_orders' => $customer->orders_count,
                'totalOrders' => $customer->orders_count,
                'ordersCount' => $customer->orders_count,
                'status' => $customer->status,
                'email' => $customer->email,
                'phone' => $customer->formatted_phone,
                'address' => $customer->address ?: 'N/A',
                'created_at' => optional($customer->created_at)->toIso8601String(),
                'memberSince' => optional($customer->created_at)->format('jS F Y'),
                'lastOrderDate' => optional($lastOrder?->order_date)->format('jS F Y'),
                'lastOrderNumber' => $lastOrder?->order_no,
                'historyUrl' => route('purchase-history.index', ['customer_id' => $customer->id]),
                'initials' => strtoupper($initials),
                'avatarColor' => $avatarPalette[$customer->id % count($avatarPalette)],
            ];
        })->values();

        return view('customers.index', compact('customersData', 'initialSearch', 'initialSort'));
    }
}
