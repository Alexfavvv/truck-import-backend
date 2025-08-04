<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Metatag;
use App\Models\PartType;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Brand::factory()->count(10)->create()
            ->each(function (Brand $brand) {
                Metatag::factory()->create([
                    'metatagable_id' => $brand->id,
                    'metatagable_type' => Brand::class,
                ]);

                $brand->partTypes()->attach(
                    PartType::all()->random(rand(1, 5))->pluck('id')
                );
            });
    }
}
