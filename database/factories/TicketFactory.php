<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_no' => 'TK-' . $this->faker->unique()->numberBetween(1000, 9999),
            'customer_id' => Customer::factory(),
            'subject' => $this->faker->sentence(4),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'closed']),
        ];
    }
}
