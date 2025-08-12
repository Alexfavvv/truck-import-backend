<?php

namespace App\Http\Filters;

use App\Models\Brand;
use App\Models\PartType;
use App\Models\TruckManufacturer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductFilter
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    protected array $keys = [
        'price',
        'brand',
        'model',
        'part',
        'sku',
    ];

    public function apply(array $data, Builder $builder): Builder
    {
        // Если в запросе SKU
        if (isset($data['sku']) && !empty($data['sku'])) {
            $this->sku($builder, $data['sku']);
            return $builder;
        }

        foreach ($this->keys as $key) {
            if (isset($data[$key])) {
                $methodName = Str::camel($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($builder, $data[$key]);
                }
            }
        }

        return $builder;
    }

    protected function price(Builder $builder, $value)
    {

        $builder->orderBy('price',  "$value");
    }

    protected function brand(Builder $builder, $value)
    {
        $brandId = Brand::query()->where('slug', $value)->pluck('id');

        if ($brandId) {
            $builder->where('brand_id', $brandId);
        }
    }

    protected function model(Builder $builder, $value)
    {
        $manufacturerId = TruckManufacturer::query()
            ->where('slug', $value)
            ->pluck('id');

        if ($manufacturerId) {
            $builder->whereHas('truckManufacturers', function ($query) use ($manufacturerId) {
                $query->where('truck_manufacturers.id', $manufacturerId);
            });
        }
    }

    protected function part(Builder $builder, $value)
    {
        $partId = PartType::query()->where('slug', $value)->pluck('id');
        if ($partId) {
            $builder->where('part_type_id', $partId);
        }
    }

    protected function sku(Builder $builder, $value)
    {
        $builder->where('sku', $value);
    }
}
