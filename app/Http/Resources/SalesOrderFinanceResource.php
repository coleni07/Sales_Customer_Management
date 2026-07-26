<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OUTPUT contract: Sales -> Finance & Accounting.
 *
 * Finance needs enough data per order to book revenue, tax, and
 * discounts against the correct GL account and reconcile against
 * customer payments. We deliberately do NOT send product-level /
 * warehouse fulfillment detail here — that belongs to the Inventory
 * export (SalesOrderFulfillmentResource) instead.
 */
class SalesOrderFinanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_no' => $this->order_no,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'customer' => [
                'customer_code' => $this->customer?->customer_code,
                'name' => $this->customer?->name,
            ],
            'financials' => [
                'subtotal' => (float) $this->subtotal,
                'discount_label' => $this->discount_label,
                'discount_amount' => (float) $this->discount_amount,
                'tax_label' => $this->tax_label,
                'tax_amount' => (float) $this->tax_amount,
                'shipping_fee' => (float) $this->shipping_fee,
                'grand_total' => (float) $this->amount,
                'currency' => 'PHP',
            ],
            'payment_method' => $this->payment_method,
            'order_status' => $this->status,
            'approval_status' => $this->approval_status,
            'gl_code' => $this->gl_code,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
