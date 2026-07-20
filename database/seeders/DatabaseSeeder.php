<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Ticket;
use App\Models\Workflow;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Pool of realistic product names to pull from when generating items for an order.
     */
    private array $productPool = [
        'Battery', 'Mouse', 'Mouse Pad', 'Keyboard', 'Speaker', 'Camera',
        'Watch', 'Charger', 'Headphones', 'Monitor', 'Webcam', 'Microphone',
        'Router', 'Power Bank', 'Flash Drive', 'HDMI Cable', 'Laptop Stand',
    ];

    /**
     * Create between 2 and 6 random items for an order, with prices that
     * add up to the order's subtotal so the numbers stay consistent.
     */
    private function seedItems(SalesOrder $order): void
    {
        $count = random_int(2, 6);
        $products = collect($this->productPool)->shuffle()->take($count)->values();
        $weights = collect(range(1, $count))->map(fn () => random_int(10, 100));
        $weightSum = $weights->sum();

        $remaining = $order->subtotal;

        foreach ($products as $i => $name) {
            $isLast = $i === $count - 1;
            $price = $isLast
                ? round($remaining, 2)
                : round(($weights[$i] / $weightSum) * $order->subtotal, 2);
            $remaining -= $price;

            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'item_name' => $name,
                'qty' => 1,
                'price' => max($price, 0),
            ]);
        }
    }

    public function run(): void
    {
        // ---- Named customers matching the mockups ----
        // Extra fields (customer_code, location, total_orders, status) belong
        // to the Customers module — merged in here so it's the SAME customer
        // row used by Sales Orders/Tickets, not a duplicate set.
        $customerDetails = [
            'Juan Dela Cruz'   => ['code' => 'CUST-001', 'location' => 'Brgy., Hugo Perez',  'phone' => '09178465394', 'total_orders' => 12, 'status' => 'Active'],
            'Maria Santos'     => ['code' => 'CUST-002', 'location' => 'Brgy., San Agustin', 'phone' => '09458132647', 'total_orders' => 10, 'status' => 'Active'],
            'Kevin Reyes'      => ['code' => 'CUST-003', 'location' => 'Brgy., Gregorio',    'phone' => '09458692475', 'total_orders' => 8,  'status' => 'Inactive'],
            'Ana Garcia'       => ['code' => 'CUST-004', 'location' => 'Brgy., Luciano',     'phone' => '09458361274', 'total_orders' => 11, 'status' => 'Active'],
            'Luiz Mendoza'     => ['code' => 'CUST-005', 'location' => 'Brgy., De Ocampo',   'phone' => '09458632147', 'total_orders' => 13, 'status' => 'Active'],
            'Sofie Lopez'      => ['code' => 'CUST-006', 'location' => 'Brgy., Cabuco',      'phone' => '09546321486', 'total_orders' => 5,  'status' => 'Active'],
            'Eloise Briderton' => ['code' => 'CUST-007', 'location' => 'Brgy., Lapidario',   'phone' => '09654832156', 'total_orders' => 7,  'status' => 'Inactive'],
        ];

        $names = array_keys($customerDetails);
        $customers = collect($names)->mapWithKeys(function ($name) use ($customerDetails) {
            $d = $customerDetails[$name];
            return [$name => Customer::factory()->create([
                'name' => $name,
                'customer_code' => $d['code'],
                'location' => $d['location'],
                'phone' => $d['phone'],
                'total_orders' => $d['total_orders'],
                'status' => $d['status'],
            ])];
        });

        // ---- Sales orders matching the "Sales Orders" screen ----
        // Dates are relative to today (e.g. "2 days ago") instead of fixed
        // calendar dates, so these orders always land inside the CURRENT
        // week/month whenever you run the seeder — keeping the Sales
        // Overview chart populated instead of showing a flat ₱0 line.
        $orders = [
            ['no' => 'SO-1001', 'cust' => 'Juan Dela Cruz', 'amount' => 3530.00, 'status' => 'pending', 'pay' => 'cod', 'approval' => 'unapproved', 'days_ago' => 0],
            ['no' => 'SO-1002', 'cust' => 'Maria Santos', 'amount' => 19250.00, 'status' => 'processing', 'pay' => 'credit', 'approval' => 'approved', 'days_ago' => 0],
            ['no' => 'SO-1003', 'cust' => 'Kevin Reyes', 'amount' => 1700.00, 'status' => 'shipped', 'pay' => 'cod', 'approval' => 'approved', 'days_ago' => 1],
            ['no' => 'SO-1004', 'cust' => 'Ana Garcia', 'amount' => 1680.00, 'status' => 'delivered', 'pay' => 'debit', 'approval' => 'approved', 'days_ago' => 1],
            ['no' => 'SO-1005', 'cust' => 'Luiz Mendoza', 'amount' => 13950.00, 'status' => 'shipped', 'pay' => 'debit', 'approval' => 'approved', 'days_ago' => 2],
            ['no' => 'SO-1006', 'cust' => 'Sofie Lopez', 'amount' => 24000.00, 'status' => 'pending', 'pay' => 'cod', 'approval' => 'unapproved', 'days_ago' => 2],
            ['no' => 'SO-1007', 'cust' => 'Eloise Briderton', 'amount' => 10300.00, 'status' => 'processing', 'pay' => 'cod', 'approval' => 'unapproved', 'days_ago' => 3],
        ];

        foreach ($orders as $o) {
            $subtotal = $o['no'] === 'SO-1001' ? 3800.00 : round($o['amount'] * 0.92, 2);
            $discount = $o['no'] === 'SO-1001' ? 300.00 : round($subtotal * 0.05, 2);
            $tax = $o['no'] === 'SO-1001' ? 250.00 : round(($subtotal - $discount) * 0.12, 2);
            $shipping = $o['no'] === 'SO-1001' ? 80.00 : 100.00;

            $salesOrder = SalesOrder::create([
                'order_no' => $o['no'],
                'customer_id' => $customers[$o['cust']]->id,
                'subtotal' => $subtotal,
                'discount_label' => '10% Corp',
                'discount_amount' => $discount,
                'tax_label' => 'VAT 12%',
                'tax_amount' => $tax,
                'shipping_fee' => $shipping,
                'amount' => $o['amount'],
                'status' => $o['status'],
                'payment_method' => $o['pay'],
                'approval_status' => $o['approval'],
                'warehouse_code' => 'W102',
                'gl_code' => 'GL-201',
                'order_date' => now()->subDays($o['days_ago']),
            ]);

            if ($o['no'] === 'SO-1001') {
                SalesOrderItem::create(['sales_order_id' => $salesOrder->id, 'item_name' => 'Speaker', 'qty' => 1, 'price' => 2300]);
                SalesOrderItem::create(['sales_order_id' => $salesOrder->id, 'item_name' => 'Watch', 'qty' => 1, 'price' => 1200]);
            } else {
                $this->seedItems($salesOrder);
            }
        }

        // ---- Tickets matching the "Latest Tickets" widget ----
        $tickets = [
            ['no' => 'TK-101', 'cust' => 'Juan Dela Cruz', 'status' => 'open'],
            ['no' => 'TK-102', 'cust' => 'Maria Santos', 'status' => 'in_progress'],
            ['no' => 'TK-103', 'cust' => 'Kevin Reyes', 'status' => 'open'],
            ['no' => 'TK-104', 'cust' => 'Ana Garcia', 'status' => 'closed'],
            ['no' => 'TK-105', 'cust' => 'Eloise Briderton', 'status' => 'closed'],
        ];

        foreach ($tickets as $t) {
            Ticket::create([
                'ticket_no' => $t['no'],
                'customer_id' => $customers[$t['cust']]->id,
                'subject' => 'Support request',
                'status' => $t['status'],
            ]);
        }

        // ---- Extra random data so charts/pagination have real volume ----
        Customer::factory(20)->create()->each(function ($customer) {
            SalesOrder::factory(random_int(1, 4))->create(['customer_id' => $customer->id])
                ->each(function ($order) {
                    $this->seedItems($order);
                });
            Ticket::factory(random_int(0, 2))->create(['customer_id' => $customer->id]);
        });

        // ---- MCM module: sample campaigns ----
        $campaigns = [
            ['name' => 'Summer Sale Blast', 'type' => 'Email', 'objective' => 'Sales', 'channel' => 'Email', 'audience' => 'All Customers', 'subject_line' => 'Summer Sale is here!', 'message' => 'Enjoy up to 30% off this summer.', 'send_date' => '2026-05-07', 'status' => 'scheduled'],
            ['name' => 'Weekend Flashsale', 'type' => 'SMS', 'objective' => 'Sales', 'channel' => 'TikTok', 'audience' => 'New Leads', 'subject_line' => 'Weekend Flash Sale!', 'message' => '48-hour flash sale, don\'t miss out.', 'send_date' => '2026-06-12', 'status' => 'scheduled'],
            ['name' => 'New Product SMS', 'type' => 'SMS', 'objective' => 'Awareness', 'channel' => 'SMS', 'audience' => 'Existing Customers', 'subject_line' => 'New Product Launch', 'message' => 'Check out our newest product line.', 'send_date' => '2026-05-24', 'status' => 'draft'],
            ['name' => 'Follow us on Instagram!', 'type' => 'Social', 'objective' => 'Engagement', 'channel' => 'Instagram', 'audience' => 'All Customers', 'subject_line' => 'Follow us!', 'message' => 'Stay updated with our latest posts.', 'send_date' => '2026-05-29', 'status' => 'scheduled'],
            ['name' => 'Loyalty Reward', 'type' => 'Email', 'objective' => 'Retention', 'channel' => 'Email', 'audience' => 'Loyalty Members', 'subject_line' => 'A reward just for you', 'message' => 'Enjoy this exclusive loyalty reward.', 'send_date' => '2026-06-01', 'status' => 'draft'],
        ];

        foreach ($campaigns as $c) {
            Campaign::create($c);
        }

        // ---- MCM module: sample workflows ----
        $workflows = [
            ['name' => 'Welcome Series', 'trigger' => 'New Signup', 'status' => 'active', 'action' => 'Send Email', 'audience' => 'New Leads', 'leads_enrolled' => 120],
            ['name' => 'Cart Abandonment', 'trigger' => 'Incomplete Checkout', 'status' => 'active', 'action' => 'Send SMS', 'audience' => 'Website Visitors', 'leads_enrolled' => 85],
            ['name' => 'Re-engagement', 'trigger' => 'Inactive 30 Days', 'status' => 'paused', 'action' => 'Send Email', 'audience' => 'Dormant Customers', 'leads_enrolled' => 47],
            ['name' => 'Birthday Offer', 'trigger' => 'Customer Birthday', 'status' => 'active', 'action' => 'Send Email', 'audience' => 'All Customers', 'leads_enrolled' => 63],
        ];

        foreach ($workflows as $w) {
            Workflow::create($w);
        }

        // ---- Support System module (integrated from the SCM sub-module) ----
        $this->call(SupportTicketSeeder::class);

        // ---- Customers module: purchase history orders ----
        // First order matches the teammate's original seed data exactly.
        $order = Order::create([
            'customer_id' => $customers['Juan Dela Cruz']->id,
            'order_number' => '12345678',
            'payment_date' => '2026-06-18',
        ]);
        $juanItems = [
            ['product_name' => 'Marshall Speaker', 'store_name' => 'Mar Studios', 'quantity' => 1, 'price' => 23402.00, 'icon' => 'fa-volume-high', 'status' => 'delivered', 'expected_delivery' => '2026-03-23'],
            ['product_name' => 'Fujifilm XF10', 'store_name' => 'Cams Studios', 'quantity' => 1, 'price' => 39169.00, 'icon' => 'fa-camera', 'status' => 'delivered', 'expected_delivery' => '2026-03-23'],
            ['product_name' => 'Marshall Wireless Headphones', 'store_name' => 'Mar Studios', 'quantity' => 1, 'price' => 9668.00, 'icon' => 'fa-headphones-simple', 'status' => 'cancelled', 'expected_delivery' => '2026-03-26'],
        ];
        foreach ($juanItems as $item) {
            OrderItem::create(['order_id' => $order->id, ...$item]);
        }

        // A few more orders across other named customers, for a fuller demo
        // of the purchase-history filters (All / Completed / Cancelled).
        $moreOrders = [
            ['cust' => 'Maria Santos', 'order_number' => '20450112', 'payment_date' => '2026-06-02', 'items' => [
                ['product_name' => 'Logitech Wireless Mouse', 'store_name' => 'Click Hub', 'quantity' => 2, 'price' => 1250.00, 'icon' => 'fa-computer-mouse', 'status' => 'delivered', 'expected_delivery' => '2026-06-08'],
                ['product_name' => 'Mechanical Keyboard', 'store_name' => 'Click Hub', 'quantity' => 1, 'price' => 3499.00, 'icon' => 'fa-keyboard', 'status' => 'delivered', 'expected_delivery' => '2026-06-08'],
            ]],
            ['cust' => 'Kevin Reyes', 'order_number' => '20450198', 'payment_date' => '2026-05-20', 'items' => [
                ['product_name' => 'Canon EOS M50', 'store_name' => 'Cams Studios', 'quantity' => 1, 'price' => 42990.00, 'icon' => 'fa-camera-retro', 'status' => 'cancelled', 'expected_delivery' => '2026-05-27'],
            ]],
            ['cust' => 'Ana Garcia', 'order_number' => '20450233', 'payment_date' => '2026-06-10', 'items' => [
                ['product_name' => 'Smart Watch Series 5', 'store_name' => 'Time Zone', 'quantity' => 1, 'price' => 8990.00, 'icon' => 'fa-clock', 'status' => 'delivered', 'expected_delivery' => '2026-06-15'],
            ]],
        ];

        foreach ($moreOrders as $o) {
            $order = Order::create([
                'customer_id' => $customers[$o['cust']]->id,
                'order_number' => $o['order_number'],
                'payment_date' => $o['payment_date'],
            ]);
            foreach ($o['items'] as $item) {
                OrderItem::create(['order_id' => $order->id, ...$item]);
            }
        }

        // ---- Sales Report module: teammate's own seeder (regions, products, representatives, ~200 days of sales) ----
        $this->call(SalesReportSeeder::class);
    }
}
