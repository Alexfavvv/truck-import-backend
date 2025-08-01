<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

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
