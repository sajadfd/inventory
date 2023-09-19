<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Entities\Contract;

class PenaltyFactory extends Factory
{
    protected $model = \Modules\HR\Entities\Penalty::class;

    public function definition()
    {
        $contract = Contract::query()->isNotEnded()->inRandomOrder()->first() ?: Contract::factory()->active()->createOne();
        return [
            'contract_id' => $contract->id,
            'notes' => 'عقوبة',
            'price' => rand(1000, 100000),
            'currency_value' => 1450,
            'currency' => 'iqd',
            'date' => $this->faker->dateTimeBetween($contract->start_date, $contract->end_date, 'Asia/Baghdad')
        ];
    }
}

