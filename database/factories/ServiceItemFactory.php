<?php

namespace Database\Factories;

use App\Models\GlobalOption;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceItem>
 */
class ServiceItemFactory extends Factory
{

    public function definition(): array
    {
        $service = Service::query()->inRandomOrder()->first() ?: Service::factory()->createOne();
        return [
            'service_id' => $service->id,
            'count' => $cnt = 1,
            'price' => $price = $service->price,
            'currency' => 'iqd',
            'total_price' => $cnt * $price,
            'currency_value' => GlobalOption::GetCurrencyValue(),
        ];
    }
}
