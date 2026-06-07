<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'discount_percentage' => fake()->randomFloat(2, 0, 15),
            'description' => fake()->sentence(),
        ];
    }
}
