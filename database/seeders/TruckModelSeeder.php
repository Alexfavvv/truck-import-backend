<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Metatag;
use App\Models\TruckModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TruckModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TruckModel::factory()->count(20)->create([
                'brand_id' => Brand::all()->random()->id
        ])->each(function (TruckModel $truckModel) {
            // Создаем метатеги для модели грузовика
            Metatag::factory()->create([
                'metatagable_id' => $truckModel->id,
                'metatagable_type' => TruckModel::class,
            ]);
        });
    }
}
