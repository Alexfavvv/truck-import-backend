<?php

namespace Database\Factories;

use App\Models\TruckManufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TruckManufacturer>
 */
class TruckManufacturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = TruckManufacturer::class;
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->word(),
            'slug' => fake()->slug(),
        ];
    }

}
