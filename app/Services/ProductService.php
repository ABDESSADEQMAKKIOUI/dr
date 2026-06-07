<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Liste produits avec filtres
     */
    public function list(array $filters = [])
    {
        $query = Product::with('category', 'brand', 'unit', 'variants', 'images');

        if (!empty(trim($filters['search'] ?? ''))) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'in_stock':
                    $query->whereColumn('stock_quantity', '>', 'stock_alert');
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)
                          ->whereColumn('stock_quantity', '<=', 'stock_alert');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
            }
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    /**
     * Créer produit
     */
    public function create(array $data): Product
    {
        DB::beginTransaction();
        try {
            $product = Product::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null,
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? $this->generateBarcode(),
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'],
                'sale_unit_id' => $data['sale_unit_id'] ?? $data['unit_id'],
                'purchase_unit_id' => $data['purchase_unit_id'] ?? $data['unit_id'],
                'type' => $data['type'] ?? 'simple',
                'cost_price' => $data['cost_price'],
                'sale_price' => $data['sale_price'],
                'tax_type' => $data['tax_type'] ?? 'exclusive',
                'tax_rate' => $data['tax_rate'] ?? 0,
                'stock_alert' => $data['stock_alert'] ?? 10,
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'description' => $data['description'] ?? null,
                'image' => $data['image'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
                'track_stock' => $data['track_stock'] ?? true,
                'has_expiry' => $data['has_expiry'] ?? false,
                'expiry_date' => $data['expiry_date'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Images
            if (isset($data['images'])) {
                $this->handleImages($product, $data['images']);
            }

            DB::commit();
            return $product->fresh('category', 'brand', 'unit', 'variants', 'images');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour produit
     */
    public function update(Product $product, array $data): Product
    {
        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'],
                'sale_unit_id' => $data['sale_unit_id'] ?? $data['unit_id'],
                'purchase_unit_id' => $data['purchase_unit_id'] ?? $data['unit_id'],
                'type' => $data['type'] ?? $product->type,
                'cost_price' => $data['cost_price'],
                'sale_price' => $data['sale_price'],
                'tax_type' => $data['tax_type'] ?? 'exclusive',
                'tax_rate' => $data['tax_rate'] ?? 0,
                'stock_alert' => $data['stock_alert'] ?? 10,
                'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
                'track_stock' => $data['track_stock'] ?? true,
                'has_expiry' => $data['has_expiry'] ?? false,
                'expiry_date' => $data['expiry_date'] ?? null,
            ];

            if (array_key_exists('image', $data)) {
                $updateData['image'] = $data['image'];
            }

            $product->update($updateData);

            // Images
            if (isset($data['images'])) {
                $this->handleImages($product, $data['images']);
            }

            DB::commit();
            return $product->fresh('category', 'brand', 'unit', 'variants', 'images');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer produit
     */
    public function delete(Product $product): bool
    {
        // Supprimer images
        foreach ($product->images as $image) {
            Storage::delete($image->path);
        }

        return $product->delete();
    }

    /**
     * Générer code-barres automatique (EAN-13)
     */
    public function generateBarcode(): string
    {
        do {
            $barcode = '200' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Gérer les images
     */
    protected function handleImages(Product $product, array $images): void
    {
        foreach ($images as $index => $imagePath) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $imagePath,
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Import CSV
     */
    public function importCSV(string $filePath): array
    {
        $imported = 0;
        $errors = [];

        // Implementation CSV import logic
        // $data = array_map('str_getcsv', file($filePath));
        // ...

        return compact('imported', 'errors');
    }

    /**
     * Export CSV
     */
    public function exportCSV(array $filters = []): string
    {
        $products = $this->list(array_merge($filters, ['per_page' => 10000]));

        // Generate CSV
        $filename = 'products_' . date('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Implementation CSV export logic
        // ...

        return $filepath;
    }
}
