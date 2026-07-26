# Sales Module — Outbound Integration APIs

This document is the **interface contract** to hand to the Finance & Accounting
group and the Inventory/Warehouse group. It tells them exactly how to pull
data from the Sales module.

## Authentication

Every request must include:

```
X-API-KEY: <key issued to your group>
```

Keys are configured in `.env`:
- `PARTNER_KEY_FINANCE` — for the Finance & Accounting group
- `PARTNER_KEY_INVENTORY` — for the Inventory/Warehouse group

Missing/invalid key → `401 Unauthorized`.

---

## API #1 — Finance & Accounting

**Purpose:** invoice/GL data so Finance can book revenue, tax, and discounts,
and reconcile receivables.

`GET /api/v1/finance/sales-orders`

Query params (all optional): `status`, `approval_status`, `from`, `to`
(order_date range, `YYYY-MM-DD`), `order_no`, `per_page`.

`GET /api/v1/finance/sales-orders/{id}` — single order.

Sample response item:

```json
{
  "order_no": "SO-1001",
  "order_date": "2026-07-20",
  "customer": { "customer_code": "CUST-014", "name": "Acme Corp" },
  "financials": {
    "subtotal": 15000.00,
    "discount_label": "10% Corp",
    "discount_amount": 1500.00,
    "tax_label": "VAT 12%",
    "tax_amount": 1620.00,
    "shipping_fee": 200.00,
    "grand_total": 15320.00,
    "currency": "PHP"
  },
  "payment_method": "credit",
  "order_status": "processing",
  "approval_status": "approved",
  "gl_code": "GL-201",
  "updated_at": "2026-07-20T09:15:00+00:00"
}
```

---

## API #2 — Inventory / Warehouse (Logistics)

**Purpose:** which items/quantities/warehouse were sold so Inventory can
deduct stock and prepare fulfillment. Only orders with `approval_status =
approved` are returned (i.e. ready to fulfill).

`GET /api/v1/inventory/order-fulfillments`

Query params (all optional): `status`, `warehouse_code`, `from`, `to`,
`per_page`.

`GET /api/v1/inventory/order-fulfillments/{id}` — single order.

Sample response item:

```json
{
  "order_no": "SO-1001",
  "order_date": "2026-07-20",
  "warehouse_code": "W102",
  "order_status": "processing",
  "items": [
    { "item_name": "Wireless Earbuds Pro", "qty": 3 },
    { "item_name": "USB-C Fast Charger", "qty": 1 }
  ],
  "updated_at": "2026-07-20T09:15:00+00:00"
}
```

---

## What Sales needs FROM other modules (our INPUT)

For the reciprocal flows, ask each group for:

| From module          | Data we need as input                                  | Why |
|-----------------------|----------------------------------------------------------|-----|
| Inventory/Warehouse    | Current stock level & availability per product/warehouse | So Sales doesn't sell items that are out of stock |
| Procurement            | Unit cost / landed cost per product                      | Margin reporting in Sales Reports |
| Finance & Accounting    | Customer credit limit / AR status                        | To gate `credit`-payment orders |

Coordinate with those groups so they expose equivalent GET endpoints, then
we'll call them from a Sales-side integration service the same way they call
ours.
