<?php

namespace Database\Factories;

use App\Models\GlobalOption;
use App\Models\Product;
use App\Models\SaleItem;
use App\Services\ProductStoreService;
use Illuminate\Database\Eloquent\Factories\BelongsToRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    public function definition(): array
    {

        $product = Product::query()->inRandomOrder()->get()->where('store', '>', 1)->first()
            ?: Product::factory()->createOne(['store'=>2]);
        return [
            'sale_list_id' => 1,
            'product_id' => $product->id,
            'count' => $cnt = rand(1, min(3, $product->store)),
            'back_count' => 0,
            'free_count' => 0,
            'price' => $price = $product->sale_price_in_iqd,
            'total_price' => $cnt * $price,
            'currency' => 'iqd',
            'currency_value' => GlobalOption::GetCurrencyValue(),
        ];
    }

    public function configure()
    {

        return $this->afterCreating(function (SaleItem $saleItem) {

            ProductStoreService::UtilizeStoreInSale($saleItem->product, $saleItem->net_count, $saleItem,$saleItem->productUnit);
        });
    }
}
