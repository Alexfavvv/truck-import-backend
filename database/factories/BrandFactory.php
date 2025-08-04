<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Brand::class;
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'description' => fake()->text,
            'content' => '{
    "first-section": "<header><h1>Категория: Запчасти для BMW</h1></header>",
    "second-section": "<section><p>Здесь вы найдете все запчасти для автомобилей BMW, включая тормоза, диски и масла.</p></section>",
    "third-section": "<footer><p>© 2025 Все права защищены. Свяжитесь с нами для консультации.</p></footer>"
}',
            'image_path' => fake()->imageUrl,
            'slug' => fake()->unique()->slug,
            'type' => fake()->randomElement(['manufacturer', 'model', 'part']),
        ];
    }

}
