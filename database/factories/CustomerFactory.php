<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_code' => 'CUST-' . str_pad((string) $this->faker->unique()->numberBetween(100, 9999), 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'location' => 'Brgy., ' . $this->faker->lastName(),
            'total_orders' => $this->faker->numberBetween(0, 20),
            'status' => $this->faker->randomElement(['Active', 'Active', 'Active', 'Inactive']),
        ];
    }
}
