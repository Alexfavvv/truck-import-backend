<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory;

    protected $casts = ['content' => 'array'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($brand) {
            // Удаляем связанные метатеги (полиморфная связь)
            $brand->metatags()->delete();

            // Удаляем изображение бренда с диска
            if ($brand->image_path) {
                Storage::disk('public')->delete($brand->image_path);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function truckModels(): HasMany
    {
        return $this->hasMany(TruckModel::class);
    }

    public function partTypes(): BelongsToMany
    {
        return $this->belongsToMany(PartType::class, 'brand_part_type', 'brand_id', 'part_type_id');
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
