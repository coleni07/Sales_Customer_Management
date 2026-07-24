<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\SalesOrder;
use App\Models\SupportTicket;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Pulls together the most recent "needs attention" items across the
     * whole ERP (not just one module) so the topbar bell reflects real,
     * live data instead of a hardcoded placeholder list.
     */
    public function index()
    {
        $items = collect();

        // Pending sales orders — someone still needs to process these
        SalesOrder::with('customer')
            ->where('status', 'pending')
            ->latest()
            ->take(3)
            ->get()
            ->each(function ($order) use ($items) {
                $items->push([
                    'icon' => 'fa-cart-shopping',
                    'color' => 'amber',
                    'title' => "New pending order {$order->order_no}",
                    'subtitle' => $order->customer->name ?? 'Customer',
                    'time' => $order->created_at?->diffForHumans(),
                    'sort' => $order->created_at,
                    'link' => route('sales-orders.index'),
                ]);
            });

        // Open support tickets — unresolved customer issues
        SupportTicket::where('status', 'Open')
            ->latest()
            ->take(3)
            ->get()
            ->each(function ($ticket) use ($items) {
                $items->push([
                    'icon' => 'fa-headset',
                    'color' => 'rose',
                    'title' => "Open ticket {$ticket->code()}",
                    'subtitle' => $ticket->subject,
                    'time' => $ticket->created_at?->diffForHumans(),
                    'sort' => $ticket->created_at,
                    'link' => route('support.index'),
                ]);
            });

        // Campaigns scheduled to go out soon
        Campaign::where('status', 'scheduled')
            ->whereDate('send_date', '>=', Carbon::now())
            ->orderBy('send_date')
            ->take(3)
            ->get()
            ->each(function ($campaign) use ($items) {
                $items->push([
                    'icon' => 'fa-bullhorn',
                    'color' => 'blue',
                    'title' => "Campaign \"{$campaign->name}\" scheduled",
                    'subtitle' => Carbon::parse($campaign->send_date)->format('M j, Y'),
                    'time' => null,
                    'sort' => Carbon::parse($campaign->send_date),
                    'link' => route('mcm.index'),
                ]);
            });

        $sorted = $items->sortByDesc('sort')->take(6)->values()->map(function ($item) {
            unset($item['sort']);
            return $item;
        });

        return response()->json([
            'count' => $sorted->count(),
            'items' => $sorted,
        ]);
    }
}
