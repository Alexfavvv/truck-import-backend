<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\TruckModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TruckModel>
 */
class TruckModelFactory extends Factory
{
    protected $model = TruckModel::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(2, true) . ' Model';
        return [
            'brand_id' => Brand::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->optional(0.7)->paragraph(2),
            'content' => $this->faker->optional(0.3)->realText(200),
            'image_path' => $this->faker->optional(0.2)->imageUrl(640, 480, 'trucks', true),
        ];
    }
}
