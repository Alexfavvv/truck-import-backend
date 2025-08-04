<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Characteristic;
use App\Models\Metatag;
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckManufacturer;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Product::factory()->count(50)->create([
            'brand_id' => fn() => Brand::all()->random()->id,
            'part_type_id' => PartType::all()->random()->id,
        ])->each(function (Product $product) {
            // Привязываем случайное количество совместимых производителей
            $product->truckManufacturers()->attach(
                TruckManufacturer::all()->random(rand(1, 3))->pluck('id')
            );

            // Создаем 1-3 характеристики для каждого продукта
            Characteristic::factory()->count(rand(1, 3))->create([
                'product_id' => $product->id,
            ]);

            // Создаем метатеги для продукта
            Metatag::factory()->create([
                'metatagable_id' => $product->id,
                'metatagable_type' => Product::class,
            ]);
        });
    }
}
