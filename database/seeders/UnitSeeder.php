<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_name' => 'pc', 'operator' => '*', 'operation_value' => 1],
            ['name' => 'Kilogram', 'short_name' => 'kg', 'operator' => '*', 'operation_value' => 1],
            ['name' => 'Gram', 'short_name' => 'g', 'operator' => '/', 'operation_value' => 1000],
            ['name' => 'Meter', 'short_name' => 'm', 'operator' => '*', 'operation_value' => 1],
            ['name' => 'Centimeter', 'short_name' => 'cm', 'operator' => '/', 'operation_value' => 100],
            ['name' => 'Liter', 'short_name' => 'L', 'operator' => '*', 'operation_value' => 1],
            ['name' => 'Milliliter', 'short_name' => 'ml', 'operator' => '/', 'operation_value' => 1000],
            ['name' => 'Box', 'short_name' => 'box', 'operator' => '*', 'operation_value' => 1],
            ['name' => 'Dozen', 'short_name' => 'doz', 'operator' => '*', 'operation_value' => 12],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['short_name' => $unit['short_name']],
                $unit
            );
        }
    }
}
