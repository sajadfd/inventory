<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stockholder>
 */
class StockholderFactory extends Factory
{

    public function definition(): array
    {
        $faker = \Faker\Factory::create('ar_SA'); // create a French faker

        return [
            'name' => $faker->unique()->name(),
            'inventory_stocks' => rand(0, 20),
            'store_stocks' => rand(0, 20),
        ];
    }
}
