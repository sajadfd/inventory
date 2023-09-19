<?php

namespace Database\Factories;

use App\Models\GlobalOption;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\BelongsToRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::query()->inRandomOrder()->first() ?: ProductFactory::new()->createOne();

        return [
            'purchase_list_id' => 1,
            'product_id' => $product->id,
            'count' => $cnt = rand(1, 10),
            'price' => $price = min($product->initialStore?->price + rand(0, 1), $product->sale_price - 0.1),
            'total_price' => $cnt * $price,
            'currency' => 'usd',
            'currency_value' => GlobalOption::GetCurrencyValue(),
        ];
    }
}
