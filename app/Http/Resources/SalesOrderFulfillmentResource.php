<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OUTPUT contract: Sales -> Inventory / Warehouse (Logistics).
 *
 * Inventory needs to know WHAT was sold, HOW MUCH, and from WHICH
 * warehouse, so they can deduct stock and plan fulfillment/shipping.
 * They do not need pricing, tax, or GL data — that stays in the
 * Finance export.
 */
class SalesOrderFulfillmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_no' => $this->order_no,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'warehouse_code' => $this->warehouse_code,
            'order_status' => $this->status,
            'items' => $this->items->map(fn ($item) => [
                'item_name' => $item->item_name,
                'qty' => $item->qty,
            ]),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
