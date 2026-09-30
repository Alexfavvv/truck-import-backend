<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'supplier_price' => 'decimal:2', 'automatic_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'manual_price' => 'decimal:2',
        'exchange_rate' => 'decimal:8', 'price_multiplier' => 'decimal:8', 'markup_percent' => 'decimal:8',
        'weight' => 'decimal:6', 'width' => 'decimal:6', 'height' => 'decimal:6', 'length' => 'decimal:6',
        'price_updated_at' => 'datetime',
    ];

    // Old callers (orders, cart, admin) receive the authoritative decimal sale price.
    public function getPriceAttribute($legacyPrice)
    {
        return $this->selling_price ?? $legacyPrice;
    }

    public function getRouteKeyName()
    {
        return 'sku';
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            // Удаляем связанные метатеги (полиморфная связь)
            $product->metatags()->delete();

            // Удаляем изображение продукта с диска
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }

    public function truckManufacturers(): BelongsToMany
    {
        return $this->belongsToMany(TruckManufacturer::class, 'product_truck_manufacturer', 'product_id', 'truck_manufacturer_id');
    }

    public function characteristics(): HasMany
    {
        return $this->hasMany(Characteristic::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    public function metatags(): MorphOne
    {
        return $this->morphOne(Metatag::class, 'metatagable');
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

}
