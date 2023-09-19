<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductLocation>
 */
class ProductLocationFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentences(1, true),
        ];
    }
}
