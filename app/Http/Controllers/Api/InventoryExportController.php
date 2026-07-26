<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalesOrderFulfillmentResource;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * REST API #2 — Sales -> Inventory / Warehouse (Logistics)
 *
 * GET /api/v1/inventory/order-fulfillments
 *
 * Lets the Inventory/Warehouse group pull newly approved sales orders
 * (items, quantities, warehouse code) so they can deduct stock and
 * prepare shipments. Read-only, filterable.
 *
 * Response envelope matches the class-wide format:
 *   { status, source_module, target_module, data_count, payload: [...] }
 *
 * Query params:
 *   status          pending|processing|shipped|delivered|cancelled
 *   warehouse_code  e.g. W102
 *   from / to       order_date range, format Y-m-d
 */
class InventoryExportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled',
            'warehouse_code' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $orders = SalesOrder::query()
            ->with('items')
            ->where('approval_status', 'approved') // only approved orders are ready to fulfill
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->warehouse_code, fn ($q, $v) => $q->where('warehouse_code', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->orderByDesc('order_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'source_module' => 'Sales',
            'target_module' => 'Inventory & Warehouse',
            'data_count' => $orders->count(),
            'payload' => SalesOrderFulfillmentResource::collection($orders),
        ]);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->load('items');

        return response()->json([
            'status' => 'success',
            'source_module' => 'Sales',
            'target_module' => 'Inventory & Warehouse',
            'data_count' => 1,
            'payload' => new SalesOrderFulfillmentResource($salesOrder),
        ]);
    }
}
