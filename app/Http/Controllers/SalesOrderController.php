<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        // Full list, not paginated server-side — the table now does live
        // search/sort/pagination entirely in the browser (same pattern as
        // the Support System page), so the search box can filter across
        // every order instantly as the user types, not just the current page.
        $orders = SalesOrder::with('customer')
            ->latest('order_date')
            ->latest('id')
            ->get();

        $ordersData = $orders->map(fn ($order) => [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'customer_name' => $order->customer->name,
            'amount' => (float) $order->amount,
            'amount_label' => number_format($order->amount, 2),
            'status' => $order->status,
            'status_label' => ucfirst($order->status),
            'status_classes' => $order->statusColor(),
            'payment_label' => $order->paymentLabel(),
            'order_date' => $order->order_date?->toIso8601String(),
        ])->values();

        // Status tracking summary cards (Pending / Processing / Shipped / Delivered)
        $statusSummary = collect(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->map(function ($status) {
            return [
                'status' => $status,
                'count' => SalesOrder::where('status', $status)
                    ->count(),
                'total' => (float) SalesOrder::where('status', $status)
                    ->sum('amount'),
            ];
        });

        $approvedCount = SalesOrder::where('approval_status', 'approved')->count();
        $unapprovedCount = SalesOrder::where('approval_status', 'unapproved')->count();

        // A notification link passes ?highlight=<id> to point at one
        // specific order — default selected order = that one when present,
        // otherwise just the most recent order.
        $highlightId = $request->query('highlight');
        $selectedId = ($highlightId && $orders->firstWhere('id', (int) $highlightId))
            ? (int) $highlightId
            : $orders->first()?->id;

        $order = $selectedId
            ? SalesOrder::with(['customer', 'items.product'])->find($selectedId)
            : null;

        $selectedOrder = $order ? collect([
            'id' => $order->id,
            'order_no' => $order->order_no,
            'customer' => $order->customer->name,
            'status' => $order->status,
            'status_label' => ucfirst($order->status),
            'approval_status' => $order->approval_status,
            'items' => $order->items->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->product?->name ?? 'Unknown Product',
                'qty' => $i->qty,
                'price' => number_format($i->price, 2),
            ]),
            'subtotal' => number_format((float) $order->subtotal, 2),
            'discount_label' => $order->discount_label,
            'discount_amount' => number_format((float) $order->discount_amount, 2),
            'tax_label' => $order->tax_label,
            'tax_amount' => number_format((float) $order->tax_amount, 2),
            'shipping_fee' => number_format((float) $order->shipping_fee, 2),
            'amount' => number_format((float) $order->amount, 2),
            'warehouse_code' => $order->warehouse_code,
            'gl_code' => $order->gl_code,
        ]) : null;

        return view('sales-orders.index', compact(
            'ordersData',
            'statusSummary',
            'approvedCount',
            'unapprovedCount',
            'selectedOrder'
        ));
    }

    /**
     * AJAX endpoint: returns the order detail panel (right sidebar) as JSON
     * so a row click can update it without a full page reload.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product']);

        return response()->json([
            'order_no' => $salesOrder->order_no,
            'customer' => $salesOrder->customer->name,
            'status' => $salesOrder->status,
            'status_label' => ucfirst($salesOrder->status),
            'approval_status' => $salesOrder->approval_status,
            'items' => $salesOrder->items->map(fn($i) => [
                'id' => $i->id, // Added for Alpine's :key loop
                'name' => $i->product?->name ?? 'Unknown Product', // Corrected relationship
                'qty' => $i->qty,
                'price' => number_format($i->price, 2),
            ]),
            'subtotal' => number_format((float) $salesOrder->subtotal, 2),
            'discount_label' => $salesOrder->discount_label,
            'discount_amount' => number_format((float) $salesOrder->discount_amount, 2),
            'tax_label' => $salesOrder->tax_label,
            'tax_amount' => number_format((float) $salesOrder->tax_amount, 2),
            'shipping_fee' => number_format((float) $salesOrder->shipping_fee, 2),
            'amount' => number_format((float) $salesOrder->amount, 2),
            'warehouse_code' => $salesOrder->warehouse_code,
            'gl_code' => $salesOrder->gl_code,
        ]);
    }

    /**
     * Saves an edit made through the website (status / approval status)
     * back to the sales_orders table in MySQL. This is what proves the
     * website can UPDATE the database, not just read from it.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'approval_status' => 'required|in:approved,unapproved',
        ]);

        $salesOrder->update($validated);

        return response()->json([
            'message' => 'Order updated successfully.',
            'status' => $salesOrder->status,
            'status_label' => ucfirst($salesOrder->status),
            'approval_status' => $salesOrder->approval_status,
        ]);
    }
}
