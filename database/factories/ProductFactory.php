<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->randomNumber(),
            'brand_id' => Brand::factory()->create(),
            'category_id' => Category::factory()->create(),
            'title' => fake()->title,
            'description' => fake()->text,
            'price' => fake()->realTextBetween(5, 10),
            'delivery' => fake()->realTextBetween(5, 10),
            'quantity' => fake()->numberBetween(0, 1000),
            'image_path' => fake()->imageUrl,
        ];
    }
}
