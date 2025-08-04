<?php

namespace Database\Seeders;

use App\Models\TruckManufacturer;
use Illuminate\Database\Seeder;

class TruckManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TruckManufacturer::factory()->count(8)->create();
    }
}
