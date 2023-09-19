<?php

namespace Database\Factories;

use App\Enums\SaleType;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Diagnosis;
use App\Models\Mechanic;
use App\Models\Order;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\ServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleList>
 */
class SaleListFactory extends Factory
{
    protected static bool $withItems = true;

    public function definition(): array
    {
        $typeIndex = rand(0, 1);


        $customer = Customer::query()
            ->when($typeIndex === 0, fn($q) => $q->whereHas('cars'))
            ->inRandomOrder()->first() ?: Customer::factory()->createOne();

        return [
            'customer_id' => $customer->id,
            'car_id' => $typeIndex === 0 ? $customer->cars()->inRandomOrder()->firstOrCreate(Car::factory()->makeOne(['customer_id' => $customer->id])->toArray())->id : null,
            'diagnosis_id' => $typeIndex === 0 ? Diagnosis::query()->inRandomOrder()->firstOrCreate(Diagnosis::factory()->makeOne()->toArray())->id : null,
            'mechanic_id' => $typeIndex === 0 ? Mechanic::query()->inRandomOrder()->firstOrCreate(Mechanic::factory()->makeOne()->toArray())->id : null,
            'type' => ['inventory_sale', 'store_sale'][$typeIndex],
            'date' => $this->faker->dateTimeBetween('-1 year'),
            'order_id' => ($this->faker->randomElement(Order::pluck('id')->toArray())),
            'currency' => 'iqd',
        ];
    }

    public function inventorySale(): self
    {
        return $this->state(function ($attributes) {
            $customer = Customer::find($attributes['customer_id']);
            return [
                'car_id' => $customer->cars()->inRandomOrder()->firstOrCreate(Car::factory()->makeOne(['customer_id' => $customer->id])->toArray())->id,
                'diagnosis_id' => Diagnosis::query()->inRandomOrder()->firstOrCreate(Diagnosis::factory()->makeOne()->toArray())->id,
                'mechanic_id' => Mechanic::query()->inRandomOrder()->first() ?: Mechanic::factory()->createOne(),
                'type' => 'inventory_sale',
            ];
        });
    }

    public function setWithItems($val): self
    {
        static::$withItems = $val;
        return $this;
    }

    public function configure(): self
    {
        return $this->afterCreating(function (SaleList $saleList) {
            if (static::$withItems) {
                $itemsCount = rand(1, 3);
                for ($i = 0; $i < $itemsCount; $i++) {
                    SaleItem::factory()->create(['sale_list_id' => $saleList->id]);
                }
                if ($saleList->type === SaleType::InventorySale) {
                    ServiceItem::factory(rand(1, 2))->create([
                        'sale_list_id' => $saleList->id
                    ]);
                }
            }
        });
    }
}
