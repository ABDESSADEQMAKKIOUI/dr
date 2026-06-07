<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    public function list()
    {
        return Brand::withCount('products')->get();
    }

    public function create(array $data): Brand
    {
        return Brand::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'logo' => $data['logo'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Brand $brand, array $data): Brand
    {
        $brand->update([
            'name' => $data['name'],
            'logo' => $data['logo'] ?? $brand->logo,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $brand->fresh();
    }

    public function delete(Brand $brand): bool
    {
        if ($brand->products()->count() > 0) {
            throw new \Exception('Impossible de supprimer une marque ayant des produits.');
        }

        if ($brand->logo) {
            Storage::delete($brand->logo);
        }

        return $brand->delete();
    }
}
