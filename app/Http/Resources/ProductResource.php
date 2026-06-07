<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'type' => $this->type,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'formatted_price' => $this->formatted_price,
            'profit_margin' => $this->profit_margin,
            'tax_type' => $this->tax_type,
            'tax_rate' => $this->tax_rate,
            'stock_quantity' => $this->stock_quantity,
            'stock_alert' => $this->stock_alert,
            'is_low_stock' => $this->stock_quantity <= $this->stock_alert,
            'description' => $this->description,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'brand' => $this->whenLoaded('brand', fn() => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
            ]),
            'unit' => $this->whenLoaded('unit', fn() => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'short_name' => $this->unit->short_name,
            ]),
            'variants' => $this->whenLoaded('variants'),
            'images' => $this->whenLoaded('images'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
