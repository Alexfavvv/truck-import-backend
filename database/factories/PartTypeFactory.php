<?php

namespace Database\Factories;


use App\Models\PartType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PartType>
 */
class PartTypeFactory extends Factory
{
    protected $model = PartType::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->word() . ' ' . $this->faker->unique()->word();
        return [
            'title' => $title,
            'slug' => Str::slug($title),
        ];
    }
}
