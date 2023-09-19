<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{

    public function definition(): array
    {
        $product = Product::query()->inRandomOrder()->where('store', '>', 0)->first() ?: Product::factory()->createOne(['store' => rand(3, 5)]);
        return [
            'product_id' => $product->id,
            'count' => $this->faker->numberBetween(1, $product->store),
            'cart_id' => ($this->faker->randomElement(Cart::pluck('id')->toArray())),
        ];
    }
}
