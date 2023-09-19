<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Enums\SaleType;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Diagnosis;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\ServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $type = rand(0, 1) ? SaleType::StoreSale : SaleType::InventorySale;
        $hasDriver = false;
        return [
            'type' => $type,
            'status' => OrderStatusEnum::ConfirmedByCustomer,
            'customer_id' => ($customer = Customer::query()->inRandomOrder()->first() ?: Customer::factory()->createOne())->id,
            'diagnosis_id' => $type === SaleType::InventorySale ? Diagnosis::inRandomOrder()->first()?->id ?: Diagnosis::factory() : null,
            'car_id' => $type === SaleType::InventorySale ? $customer->cars()->inRandomOrder()->first()?->id ?: Car::factory()->for($customer) : null,
            'driver_id' => $type === SaleType::StoreSale ? ($hasDriver = !!rand(0, 1) ?
                (Driver::inRandomOrder()->first()?->id ?: Driver::factory())
                : null) : null,
            'end_latitude' => $hasDriver ? '38.0' : null,
            'end_longitude' => $hasDriver ? '38.0' : null,
            'end_address' => $hasDriver ? fake('ar_SA')->address() : null,
            'currency'=>'iqd',

        ];
    }

    public function inventoryOrder()
    {
        return $this->state(function ($attributes) {
            $type = SaleType::InventorySale;
            $customer = Customer::find($attributes['customer_id']);
            return [
                'diagnosis_id' => $attributes['diagnosis_id'] ?: Diagnosis::inRandomOrder()->first()?->id ?: Diagnosis::factory(),
                'car_id' => $attributes['car_id'] ?: $customer->cars()->inRandomOrder()->first()?->id ?: Car::factory()->for($customer),
                'type' => $type,
                'driver_id' => null,
                'end_latitude' => null,
                'end_longitude' => null,
                'end_address' => null,

            ];
        });
    }

    public function storeOrder($hasDelivery = false, $hasDriver = false,)
    {
        return $this->state(function ($attributes) use ($hasDelivery, $hasDriver) {
            $type = SaleType::StoreSale;
            return [
                'type' => $type,
                'diagnosis_id' => null,
                'car_id' => null,
                'driver_id' => ($hasDelivery && $hasDriver ?
                    ($attributes['driver_id'] ?: Driver::inRandomOrder()->first()?->id ?: Driver::factory())
                    : null),
                'end_latitude' => $hasDelivery ? '38.0' : null,
                'end_longitude' => $hasDelivery ? '38.0' : null,
                'end_address' => $hasDelivery ? fake('ar_SA')->address() : null,
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $itemsCount = rand(1, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                OrderItem::factory()->for($order)->create();
            }
        });
    }
}
