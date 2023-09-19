<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Entities\Employer;
use Modules\HR\Enums\SalaryTypeEnum;
use Modules\HR\Enums\TrackByEnum;

class ContractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Entities\Contract::class;

    public function definition(): array
    {
        $start_date = fake()->dateTimeBetween('-1 year', 'now');
        $end_date = fake()->dateTimeBetween($start_date, '+2 year');
        return [
            "employer_id" => (Employer::query()->whereDoesntHave('activeContract')->inRandomOrder()->first() ?: EmployerFactory::new()->createOne())->id,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "salary_type" => fake()->randomElement(SalaryTypeEnum::getAllValues()),
            "salary_price" => fake()->randomFloat(2, 0, 999999.99),
            "salary_currency" => 'iqd',
            "day_work_hours" => fake()->numberBetween(1, 24),
            "day_work_start_hour" => fake()->randomFloat(2, 0, 24.0),
            "track_by" => fake()->randomElement(TrackByEnum::getAllValues())
        ];
    }

    public function active(): self
    {
        return $this->state(function ($attributes) {
            return [
                "end_date" => max($attributes['end_date'], now()->addYear()),
            ];
        });
    }

    public function sample1(): self
    {
        return $this->state(function ($attributes) {
            return [
                "start_date" => '2023-01-01 08:00:00',
                "end_date" => '2023-12-31 16:00:00',
                "salary_type" => SalaryTypeEnum::ByMonth,
                "salary_price" => 300000,
                "day_work_hours" => 8,
                "day_work_start_hour" => 8,
                "track_by" => TrackByEnum::Absences,
            ];
        });
    }

    public function daily1(): self
    {
        return $this->state(function ($attributes) {
            return [
                "start_date" => '2023-01-01 08:00:00',
                "end_date" => '2023-12-31 16:00:00',
                "salary_type" => SalaryTypeEnum::ByDay,
                "salary_price" => 10000,
                "day_work_hours" => 8,
                "day_work_start_hour" => 8,
                "track_by" => TrackByEnum::Absences,
            ];
        });
    }

    public function sample2(): self
    {
        return $this->state(function ($attributes) {
            return [
                "start_date" => '2023-03-06 15:00:00',
                "end_date" => '2023-09-28 16:00:00',
                "salary_type" => SalaryTypeEnum::ByMonth,
                "salary_price" => 520000,
                "day_work_hours" => 9,
                "day_work_start_hour" => 11,
                "track_by" => TrackByEnum::Absences,
            ];
        });
    }

    public function randomSample1(): self
    {
        return $this->state(function ($attributes) {
            return [
                "start_date" => "2022-10-09 05:06:38",
                "end_date" => "2023-08-08 00:50:32",
                "salary_type" => "by_day",
                "salary_price" => 912230.58,
                "salary_currency" => "iqd",
                "day_work_hours" => 23,
                "day_work_start_hour" => 20.6,
                "track_by" => "attendances",
            ];
        });
    }

}

