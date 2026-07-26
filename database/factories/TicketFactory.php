<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $scenarios = [
            ['subject' => 'Order arrived damaged', 'desc' => 'Customer received shipment with damaged exterior packaging.'],
            ['subject' => 'Delayed shipment', 'desc' => 'Order has been stuck in processing for several days.'],
            ['subject' => 'Wrong item shipped', 'desc' => 'Received incorrect item model/color in the package.'],
            ['subject' => 'Refund request', 'desc' => 'Customer requesting order cancellation and refund before dispatch.'],
            ['subject' => 'Question about warranty', 'desc' => 'Inquiring about warranty period and coverage details.'],
            ['subject' => 'Discount code error', 'desc' => 'Promo code fails to apply at checkout for bulk order.'],
            ['subject' => 'Payment charged twice', 'desc' => 'Duplicate charge noticed on bank statement for single order.'],
            ['subject' => 'Tracking link invalid', 'desc' => 'Courier tracking link returns a 404 page not found.'],
        ];

        $scenario = $this->faker->randomElement($scenarios);

        return [
            'ticket_no' => 'TK-' . $this->faker->unique()->numberBetween(3000, 9999),
            'customer_id' => Customer::factory(),
            'subject' => $scenario['subject'],
            'description' => $scenario['desc'],
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'closed']),
            'assigned_to' => $this->faker->randomElement(['Kyle Anthony', 'Dana Ruiz', 'Mark Santos']),
        ];
    }
}