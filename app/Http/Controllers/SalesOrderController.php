<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 7;

        // A notification link passes ?highlight=<id> to point at one specific
        // order. That order can live on any page of the listing (it's just
        // "the 3 latest pending orders", not necessarily page 1), so we work
        // out which page it actually falls on and jump the pagination there
        // — otherwise the detail panel would show an order that never
        // appears as a row in the table at all.
        $highlightId = $request->query('highlight');

        if ($highlightId && ! $request->query('page')) {
            $orderedIds = SalesOrder::orderByDesc('order_date')->orderByDesc('id')->pluck('id');
            $position = $orderedIds->search((int) $highlightId);

            if ($position !== false) {
                $request->query->set('page', intdiv($position, $perPage) + 1);
            }
        }

        $orders = SalesOrder::with('customer')
            ->latest('order_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // Status tracking summary cards (Pending / Processing / Shipped / Delivered)
        $statusSummary = collect(['pending', 'processing', 'shipped', 'delivered'])->map(function ($status) {
            return [
                'status' => $status,
                'count' => SalesOrder::where('status', $status)->count(),
                'total' => SalesOrder::where('status', $status)->sum('amount'),
            ];
        });

        $approvedCount = SalesOrder::where('approval_status', 'approved')->count();
        $unapprovedCount = SalesOrder::where('approval_status', 'unapproved')->count();

        // Default selected order = the highlighted order (from a notification
        // link) when it's present on this page, otherwise the first row on
        // the page (mirrors the design) — so the detail panel always matches
        // a row that's actually visible in the table.
        $selectedId = ($highlightId && $orders->firstWhere('id', (int) $highlightId))
            ? (int) $highlightId
            : $orders->first()?->id;

        $selectedOrder = $selectedId
            ? SalesOrder::with(['customer', 'items'])->find($selectedId)
            : null;

        return view('sales-orders.index', compact(
            'orders', 'statusSummary', 'approvedCount', 'unapprovedCount', 'selectedOrder'
        ));
    }

    /**
     * AJAX endpoint: returns the order detail panel (right sidebar) as JSON
     * so a row click can update it without a full page reload.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items']);

        return response()->json([
            'order_no' => $salesOrder->order_no,
            'customer' => $salesOrder->customer->name,
            'status' => $salesOrder->status,
            'status_label' => ucfirst($salesOrder->status),
            'approval_status' => $salesOrder->approval_status,
            'items' => $salesOrder->items->map(fn ($i) => [
                'name' => $i->item_name,
                'qty' => $i->qty,
                'price' => number_format($i->price, 2),
            ]),
            'subtotal' => number_format($salesOrder->subtotal, 2),
            'discount_label' => $salesOrder->discount_label,
            'discount_amount' => number_format($salesOrder->discount_amount, 2),
            'tax_label' => $salesOrder->tax_label,
            'tax_amount' => number_format($salesOrder->tax_amount, 2),
            'shipping_fee' => number_format($salesOrder->shipping_fee, 2),
            'amount' => number_format($salesOrder->amount, 2),
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
