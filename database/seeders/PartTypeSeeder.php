<?php

namespace Database\Seeders;

use App\Models\PartType;
use Illuminate\Database\Seeder;

class PartTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PartType::factory()->count(15)->create();
    }
}
