<?php

namespace Modules\HR\Database\factories;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Entities\Contract;
use Modules\HR\Services\CheckOverlappingDatesService;

class AttendanceFactory extends Factory
{

    protected $model = \Modules\HR\Entities\Attendance::class;


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

