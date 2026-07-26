<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalesOrderFinanceResource;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * REST API #1 — Sales -> Finance & Accounting
 *
 * GET /api/v1/finance/sales-orders
 *
 * Lets the Finance & Accounting group pull sales order/invoice data
 * (amounts, tax, discounts, GL code, payment method) so they can post
 * revenue and reconcile receivables. Read-only, filterable.
 *
 * Response envelope matches the class-wide format:
 *   { status, source_module, target_module, data_count, payload: [...] }
 *
 * Query params:
 *   status        pending|processing|shipped|delivered|cancelled
 *   approval_status  approved|unapproved
 *   from / to     order_date range, format Y-m-d
 *   order_no      exact match lookup for a single order
 */
class FinanceExportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled',
            'approval_status' => 'nullable|in:approved,unapproved',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'order_no' => 'nullable|string',
        ]);

        $orders = SalesOrder::query()
            ->with('customer')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->approval_status, fn ($q, $v) => $q->where('approval_status', $v))
            ->when($request->order_no, fn ($q, $v) => $q->where('order_no', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->orderByDesc('order_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'source_module' => 'Sales',
            'target_module' => 'Finance & Accounting',
            'data_count' => $orders->count(),
            'payload' => SalesOrderFinanceResource::collection($orders),
        ]);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->load('customer');

        return response()->json([
            'status' => 'success',
            'source_module' => 'Sales',
            'target_module' => 'Finance & Accounting',
            'data_count' => 1,
            'payload' => new SalesOrderFinanceResource($salesOrder),
        ]);
    }
}
