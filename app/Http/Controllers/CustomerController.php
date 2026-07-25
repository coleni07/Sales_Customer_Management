<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $customers = Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            })
            ->orderBy('id')
            ->paginate(7)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $orders = $customer->orders()
            ->with('items')
            ->latest()
            ->get();

        return view('customers.show', compact('customer', 'orders'));
    }
}
