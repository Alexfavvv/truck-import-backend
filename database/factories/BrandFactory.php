<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->company();
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->optional(0.7)->paragraph(2),
            'content' => [
                'subcat__title' => $this->faker->sentence(3),
                'first__place' => [
                    [$this->faker->words(3, true), $this->faker->randomHtml()],
                ],
                'second__place' => [
                    [$this->faker->words(3, true), $this->faker->randomHtml()],
                ],
                'faq__title' => $this->faker->sentence(2),
                'faq__data' => $this->faker->randomElements(
                    array_map(function ($i) {
                        return [$this->faker->sentence(), $this->faker->paragraph()];
                    }, range(1, 5)) // Создаем от 1 до 5 случайных FAQ
                ),
            ],
            'image_path' => $this->faker->optional(0.2)->imageUrl(640, 480, 'transport', true),
        ];
    }
}
