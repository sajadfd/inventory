<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Color>
 */
class ColorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ar_SA')->unique()->colorName(),
            'code' => Str::upper('ff' . ltrim($this->faker->unique()->hexColor(), '#')),
        ];
    }
}
