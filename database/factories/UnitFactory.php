<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        $units = [
            ['name' => 'Pièce', 'short_name' => 'Pc'],
            ['name' => 'Kilogramme', 'short_name' => 'Kg'],
            ['name' => 'Litre', 'short_name' => 'L'],
            ['name' => 'Mètre', 'short_name' => 'm'],
        ];

        $unit = fake()->randomElement($units);

        return [
            'name' => $unit['name'],
            'short_name' => $unit['short_name'],
            'operator' => '*',
            'operation_value' => 1.0000,
        ];
    }
}
