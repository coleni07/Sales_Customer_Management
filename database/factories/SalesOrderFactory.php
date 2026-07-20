<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 1000, 30000);
        $discount = round($subtotal * 0.05, 2);
        $tax = round(($subtotal - $discount) * 0.12, 2);
        $shipping = $this->faker->randomFloat(2, 50, 200);
        $total = $subtotal - $discount + $tax + $shipping;

        return [
            'order_no' => 'SO-' . $this->faker->unique()->numberBetween(10000, 99999),
            'customer_id' => Customer::factory(),
            'subtotal' => $subtotal,
            'discount_label' => '5% Corp',
            'discount_amount' => $discount,
            'tax_label' => 'VAT 12%',
            'tax_amount' => $tax,
            'shipping_fee' => $shipping,
            'amount' => $total,
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'payment_method' => $this->faker->randomElement(['cod', 'credit', 'debit']),
            'approval_status' => $this->faker->randomElement(['approved', 'unapproved']),
            'warehouse_code' => 'W' . $this->faker->numberBetween(100, 199),
            'gl_code' => 'GL-' . $this->faker->numberBetween(200, 299),
            'order_date' => $this->faker->dateTimeBetween('-21 days', 'now'),
        ];
    }
}
