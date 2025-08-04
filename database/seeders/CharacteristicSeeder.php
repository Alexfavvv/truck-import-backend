<?php

namespace Database\Seeders;

use App\Models\Characteristic;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CharacteristicSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Characteristic::factory()->count(80)->create([
            'product_id' => fn() => Product::all()->random()->id,
        ]);
    }
}
