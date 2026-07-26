<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private function seedItems(SalesOrder $order): void
    {
        if ($order->items()->count() > 0) {
            return;
        }

        $count = random_int(1, 4);
        $products = Product::inRandomOrder()->take($count)->get();
        $subtotal = 0;

        foreach ($products as $product) {
            $qty = random_int(1, 3);
            $price = $product->price;
            $subtotal += ($price * $qty);

            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id' => $product->id,
                'qty' => $qty,
                'price' => $price,
            ]);
        }

        $discount = round($subtotal * 0.05, 2);
        $tax = round(($subtotal - $discount) * 0.12, 2);
        $amount = round($subtotal - $discount + $tax + 100.00, 2);

        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'amount' => $amount,
        ]);
    }

    public function run(): void
    {
        $this->call(SalesReportSeeder::class);

        $customerDetails = [
            'Juan Dela Cruz' => ['code' => 'CUST-001', 'location' => 'Brgy., Hugo Perez', 'phone' => '09178465394', 'total_orders' => 12, 'status' => 'Active'],
            'Maria Santos' => ['code' => 'CUST-002', 'location' => 'Brgy., San Agustin', 'phone' => '09458132647', 'total_orders' => 10, 'status' => 'Active'],
            'Kevin Reyes' => ['code' => 'CUST-003', 'location' => 'Brgy., Gregorio', 'phone' => '09458692475', 'total_orders' => 8, 'status' => 'Inactive'],
            'Ana Garcia' => ['code' => 'CUST-004', 'location' => 'Brgy., Luciano', 'phone' => '09458361274', 'total_orders' => 11, 'status' => 'Active'],
            'Luiz Mendoza' => ['code' => 'CUST-005', 'location' => 'Brgy., De Ocampo', 'phone' => '09458632147', 'total_orders' => 13, 'status' => 'Active'],
            'Sofie Lopez' => ['code' => 'CUST-006', 'location' => 'Brgy., Cabuco', 'phone' => '09546321486', 'total_orders' => 5, 'status' => 'Active'],
            'Eloise Briderton' => ['code' => 'CUST-007', 'location' => 'Brgy., Lapidario', 'phone' => '09654832156', 'total_orders' => 7, 'status' => 'Inactive'],
        ];

        $customers = collect(array_keys($customerDetails))->mapWithKeys(function ($name) use ($customerDetails) {
            $d = $customerDetails[$name];
            return [
                $name => Customer::factory()->create([
                    'name' => $name,
                    'customer_code' => $d['code'],
                    'location' => $d['location'],
                    'phone' => $d['phone'],
                    'total_orders' => $d['total_orders'],
                    'status' => $d['status'],
                ])
            ];
        });

        $orders = [
            ['no' => 'SO-10001', 'cust' => 'Juan Dela Cruz', 'status' => 'pending', 'pay' => 'cod', 'approval' => 'unapproved', 'days_ago' => 0],
            ['no' => 'SO-10002', 'cust' => 'Maria Santos', 'status' => 'processing', 'pay' => 'credit', 'approval' => 'approved', 'days_ago' => 0],
            ['no' => 'SO-10003', 'cust' => 'Kevin Reyes', 'status' => 'shipped', 'pay' => 'cod', 'approval' => 'approved', 'days_ago' => 1],
            ['no' => 'SO-10004', 'cust' => 'Ana Garcia', 'status' => 'delivered', 'pay' => 'debit', 'approval' => 'approved', 'days_ago' => 1],
            ['no' => 'SO-10005', 'cust' => 'Luiz Mendoza', 'status' => 'shipped', 'pay' => 'debit', 'approval' => 'approved', 'days_ago' => 2],
            ['no' => 'SO-10006', 'cust' => 'Sofie Lopez', 'status' => 'pending', 'pay' => 'cod', 'approval' => 'unapproved', 'days_ago' => 2],
            ['no' => 'SO-10007', 'cust' => 'Eloise Briderton', 'status' => 'processing', 'pay' => 'cod', 'approval' => 'unapproved', 'days_ago' => 3],
        ];

        foreach ($orders as $o) {
            $salesOrder = SalesOrder::create([
                'order_no' => $o['no'],
                'customer_id' => $customers[$o['cust']]->id,
                'region_id' => \App\Models\Region::inRandomOrder()->value('id'),
                'representative_id' => \App\Models\Representative::inRandomOrder()->value('id'),
                'discount_label' => '5% Corp',
                'tax_label' => 'VAT 12%',
                'shipping_fee' => 100.00,
                'status' => $o['status'],
                'payment_method' => $o['pay'],
                'approval_status' => $o['approval'],
                'warehouse_code' => 'W102',
                'gl_code' => 'GL-201',
                'order_date' => now()->subDays($o['days_ago']),
            ]);

            $this->seedItems($salesOrder);
        }

        Customer::factory(20)->create()->each(function ($customer) {
            $salesOrders = SalesOrder::factory(random_int(10, 25))->create([
                'customer_id' => $customer->id,
                'region_id' => \App\Models\Region::inRandomOrder()->value('id'),
                'representative_id' => \App\Models\Representative::inRandomOrder()->value('id'),
            ]);

            $salesOrders->each(function ($order) {
                $this->seedItems($order);
            });

            $customer->update(['total_orders' => $salesOrders->count()]);

            Ticket::factory(random_int(0, 2))->create(['customer_id' => $customer->id]);
        });

        $this->call([
            CampaignSeeder::class,
            WorkflowSeeder::class,
            SupportTicketSeeder::class,
        ]);
    }
}