<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'tax_number' => fake()->numerify('TAX-########'),
            'customer_group_id' => CustomerGroup::factory(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Morocco',
            'credit_limit' => fake()->randomFloat(2, 1000, 50000),
            'loyalty_points' => fake()->numberBetween(0, 1000),
            'is_active' => true,
        ];
    }
}
