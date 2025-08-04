<?php

namespace Database\Factories;

use App\Models\TruckManufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TruckManufacturer>
 */
class TruckManufacturerFactory extends Factory
{
    protected $model = TruckManufacturer::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->company() . ' Trucks';
        return [
            'title' => $title,
            'slug' => Str::slug($title),
        ];
    }
}
