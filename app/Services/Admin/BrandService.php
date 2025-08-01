<?php

namespace App\Services\Admin;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public static function store(array $data): Brand
    {
        try {
            DB::beginTransaction();
            $brand = Brand::create($data['brand']);
            $brand->metatags()->create($data['meta']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, $e->getMessage());
        }

        return $brand;
    }

    public static function update(array $data, Brand $brand): Brand
    {
        try {
            DB::beginTransaction();
            $brand->update($data['brand']);
            $brand->metatags()->updateOrCreate($data['meta']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, $e->getMessage());
        }

        return $brand;
    }
}
