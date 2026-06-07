<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function list()
    {
        return Category::with('parent', 'children')->withCount('products')->get();
    }

    public function create(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'code' => $data['code'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? $category->image,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        if ($category->children()->count() > 0) {
            throw new \Exception('Impossible de supprimer une catégorie ayant des sous-catégories.');
        }

        if ($category->products()->count() > 0) {
            throw new \Exception('Impossible de supprimer une catégorie ayant des produits.');
        }

        if ($category->image) {
            Storage::delete($category->image);
        }

        return $category->delete();
    }

    public function getTree()
    {
        return Category::whereNull('parent_id')
            ->with('children.children')
            ->orderBy('sort_order')
            ->get();
    }
}
