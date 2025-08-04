<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\PartType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);
        return [
            'part_type_id' => PartType::factory(),
            'brand_id' => Brand::factory(),
            'sku' => $this->faker->unique()->ean13(),
            'title' => $title,
            'description' => $this->faker->optional(0.8)->paragraph(3),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'delivery' => $this->faker->optional(0.5)->numberBetween(1, 14) . ' дней',
            'quantity' => $this->faker->numberBetween(0, 500),
            'image_path' => $this->faker->optional(0.3)->imageUrl(640, 480, 'parts', true),
        ];
    }

    // Состояние для создания продукта без изображения
    public function withoutImage(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => null,
        ]);
    }
}
