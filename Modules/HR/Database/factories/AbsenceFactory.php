<?php

namespace Modules\HR\Database\factories;

use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Modules\HR\Entities\Contract;
use Modules\HR\Services\CheckOverlappingDatesService;

class AbsenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Entities\Absence::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $contract = Contract::query()->isNotEnded()->inRandomOrder()->first() ?: ContractFactory::new()->createOne();
        [$start_date, $end_date] = CheckOverlappingDatesService::GetCleanDatePeriod($contract);
        return [
            "contract_id" => $contract->id,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "notes" => rand(0, 1) ? fake()->sentences(1, true) : null,
        ];
    }

}
