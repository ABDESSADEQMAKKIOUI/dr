<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 10, 500);
        $salePrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);

        return [
            'name' => fake()->words(3, true),
            'sku' => 'PRD-' . strtoupper(fake()->unique()->bothify('########')),
            'barcode' => fake()->ean13(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'unit_id' => Unit::factory(),
            'type' => 'simple',
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'tax_type' => 'exclusive',
            'tax_rate' => 20.00,
            'stock_alert' => 10,
            'stock_quantity' => fake()->numberBetween(0, 100),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'track_stock' => true,
        ];
    }
}
