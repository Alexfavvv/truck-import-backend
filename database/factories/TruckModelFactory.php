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
            'content' => [
                'faq__title' => $this->faker->optional(0.8)->sentence(2),
                'faq__data' => $this->faker->optional(0.8)->randomElements(
                    array_map(function ($i) {
                        return [$this->faker->sentence(), $this->faker->paragraph()];
                    }, range(1, 3))
                ),
                'first__place' => $this->faker->optional(0.8)->randomElements(
                    array_map(function ($i) {
                        return [$this->faker->words(3, true), $this->faker->randomHtml()];
                    }, range(1, 3))
                ),
                'second__place' => $this->faker->optional(0.8)->randomElements(
                    array_map(function ($i) {
                        return [$this->faker->words(3, true), $this->faker->randomHtml()];
                    }, range(1, 3))
                ),
            ],
            'image_path' => $this->faker->optional(0.2)->imageUrl(640, 480, 'trucks', true),
        ];
    }
}
