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
            'content' => $this->faker->optional(0.3)->realText(200),
            'image_path' => $this->faker->optional(0.2)->imageUrl(640, 480, 'transport', true),
        ];
    }
}
