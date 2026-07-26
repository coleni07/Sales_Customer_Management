<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
<<<<<<< HEAD
        $sort = $request->query('sort', 'name_asc');

        $sortMap = [
            'name_asc'  => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'date_new'  => ['created_at', 'desc'],
            'date_old'  => ['created_at', 'asc'],
        ];

        [$sortColumn, $sortDirection] = $sortMap[$sort] ?? $sortMap['name_asc'];

        $customers = Customer::query()
            ->withCount('orders')
            ->with(['orders' => function ($query) {
                $query->latest('payment_date')->limit(1);
            }])
=======
        $customers = Customer::query()
            ->withCount('orders')
>>>>>>> 50f2c6f377d6f45668e2d0dcc1b500235ec95d45
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            })
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(7)
            ->withQueryString();

        return view('customers.index', compact('customers', 'sort'));
    }
}