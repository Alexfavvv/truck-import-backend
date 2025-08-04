<?php

namespace Database\Factories;

use App\Models\Characteristic;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Characteristic>
 */
class CharacteristicFactory extends Factory
{
    protected $model = Characteristic::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(), // Автоматически создаст продукт, если не указан
            'title' => $this->faker->word(),
            'description' => $this->faker->optional(0.8)->sentence(),
        ];
    }
}
