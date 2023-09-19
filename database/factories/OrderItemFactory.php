<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SaleItem;
use App\Services\ProductStoreService;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::query()->inRandomOrder()->where('store', '>', 0)->first() ?: Product::factory()->createOne(['store' => rand(3, 5)]);

        return [
            'product_id' => $product->id,
            'price' => $price = $product->sale_price_in_iqd,
            'count' => $count = $this->faker->numberBetween(1, $product->store),
            'total_price' => $count * $price,
            'order_id' => Order::inRandomOrder()->first()?->id ?: Order::factory(),
            'currency' => 'iqd',
            'currency_value' => 1450,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (OrderItem $orderItem) {
            ProductStoreService::UtilizeStoreInSale($orderItem->product, $orderItem->count, $orderItem,$orderItem->productUnit);
        });
    }
}
