<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseList>
 */
class PurchaseListFactory extends Factory
{

    public static bool $withItems = true;
    public static bool $isConfirmed = false;
    public static bool $isPayed = false;
    public static bool $isPartiallyPayed = false;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $supplier = Supplier::query()->inRandomOrder()->first() ?: Supplier::factory()->createOne();
        return [
            'supplier_id' => $supplier->id,
            'date' => $this->faker->dateTimeBetween('-1 year'),
            'currency' => 'usd',
        ];
    }

    public function setWithItems($val): self
    {
        static::$withItems = $val;
        return $this;
    }


    public function setIsConfirmed(bool $state): self
    {
        static::$isConfirmed = $state;
        return $this;
    }

    public static function setIsPayed(bool $state): self
    {
        static::$isPayed = $state;
        return new static();
    }

    public static function setIsPartiallyPayed(bool $state): self
    {
        static::$isPartiallyPayed = $state;
        return new static();
    }

    public function configure(): self
    {
        return $this->afterCreating(function (PurchaseList $purchaseList) {
            if (static::$withItems) {
                PurchaseItem::factory(rand(3, 5))->create([
                    'purchase_list_id' => $purchaseList->id
                ]);
                if (static::$isConfirmed) {
                    $purchaseList->confirm(static::$isPayed);

                    if (!static::$isPayed && static::$isPartiallyPayed) {
                        /** @var Bill $bill */
                        $bill = $purchaseList->bill()->first();
                        $numberOfPayments = fake()->numberBetween(1, 4);
                        $paymentPrice = round($purchaseList->total_price / $numberOfPayments, 2);
                        for ($i = 1; $i < $numberOfPayments; $i++) {
                            $bill->pay($paymentPrice);
                        }
                    }
                }
            }
        });

    }
}
