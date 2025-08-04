<?php

namespace Database\Factories;

use App\Models\Metatag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Metatag>
 */
class MetatagFactory extends Factory
{
    protected $model = Metatag::class;

    public function definition(): array
    {
        return [
            // metatagable_id и metatagable_type будут установлены при создании
            'meta_h1' => $this->faker->optional(0.7)->sentence(3),
            'meta_title' => $this->faker->optional(0.8)->sentence(5),
            'meta_description' => $this->faker->optional(0.9)->paragraph(1),
        ];
    }
}
