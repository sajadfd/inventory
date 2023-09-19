<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Entities\Contract;
use Modules\HR\Services\CheckOverlappingDatesService;

class OffDateFactory extends Factory
{

    protected $model = \Modules\HR\Entities\OffDate::class;


    public function definition()
    {
        $contract = Contract::query()->isNotEnded()->inRandomOrder()->first() ?: ContractFactory::new()->createOne();
        [$start_date, $end_date] = CheckOverlappingDatesService::GetCleanDatePeriod($contract);

    return [
            "contract_id" => $contract->id ,
            "start_date" => $start_date ,
            "end_date" => $end_date ,
            "notes" => fake()->paragraph() ,
        ];
    }
}

