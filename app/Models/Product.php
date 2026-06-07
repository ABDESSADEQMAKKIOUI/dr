<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'unit_id',
        'sale_unit_id',
        'purchase_unit_id',
        'type',
        'cost_price',
        'sale_price',
        'tax_type',
        'tax_rate',
        'stock_alert',
        'stock_quantity',
        'description',
        'image',
        'gallery',
        'is_active',
        'is_featured',
        'track_stock',
        'has_expiry',
        'expiry_date',
        'created_by',
        'has_serial_numbers',
        'has_warranty',
        'warranty_duration',
        'warranty_type',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'stock_alert' => 'integer',
            'stock_quantity' => 'integer',
            'gallery' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'track_stock' => 'boolean',
            'has_expiry' => 'boolean',
            'expiry_date' => 'date',
            'has_serial_numbers' => 'boolean',
            'has_warranty' => 'boolean',
            'warranty_duration' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'stock_alert');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', 0);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function saleUnit()
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function warehouses()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getProfitMarginAttribute()
    {
        if ($this->cost_price == 0) return 0;
        return (($this->sale_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->sale_price, 2) . ' MAD';
    }
}
