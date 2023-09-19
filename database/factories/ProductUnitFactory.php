<?php

namespace Database\Factories;

use App\Enums\ProductUnitType;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductUnit>
 */
class ProductUnitFactory extends Factory
{

    public function definition(): array
    {
        return [
            'product_id' => $product = Product::query()->inRandomOrder()->first() ?: Product::factory()->createOne(),
            'name' => fake()->word(),
            'type' => $type = fake()->randomElement(ProductUnitType::cases()),
            'factor' => $factor = rand(2, 5),
            'price' => match ($type) {
                ProductUnitType::smaller => $product->sale_price / $factor,
                ProductUnitType::larger => $product->sale_price * $factor,
            },
            'currency' => $product->sale_currency,
        ];
    }

}
