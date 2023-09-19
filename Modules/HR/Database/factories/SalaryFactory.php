<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Salary;
use Modules\HR\Enums\SalaryTypeEnum;

class SalaryFactory extends Factory
{

    protected $model = \Modules\HR\Entities\Salary::class;

    public function definition(): array
    {
        /** @var Contract $contract */
        $contract =
            Contract::query()->inRandomOrder()->first() ?:
                Contract::factory()->createOne();

        /** @var Salary $lastSalary */
        $lastSalary = $contract->salaries()->orderBy('end_date', 'desc')->first();

        return [
            'contract_id' => $contract->id,
            'price' => $contract->salary_price,
            'currency' => $contract->salary_currency,
            'type' => $contract->salary_type,
            'start_date' => $startDate = $lastSalary?->start_date->clone()->addMinute() ?: $contract->start_date->clone(),
            'end_date' => $endDate = match ($contract->salary_type) {
                SalaryTypeEnum::ByMonth => $startDate->clone()->addMonth(),
                default => $startDate->clone()->addDay(),
            },
            'worked_days' => $workedDays = $startDate->diffInDays($endDate),
            'worked_hours' => $workedDays * $contract->day_work_hours,
            'due_date' => $dueDate = $endDate,
            'is_payed' => $isPayed = !rand(0, 1),
            'payed_at' => $isPayed ? fake()->dateTimeBetween($dueDate, max($dueDate, now())) : null,
            'notes' => rand(0, 1) ? $this->faker->sentences(2, true) : null,
            'code' => Str::uuid()->toString(),
            'currency_value' => 1450,
        ];
    }

    public function paidStatus($isPayed): self
    {
        return $this->state(function ($attributes) use ($isPayed) {
            return [
                'is_payed' => $isPayed,
                'payed_at' => $isPayed ? fake()->dateTimeBetween($attributes['due_date'], max($attributes['due_date'], now())) : null,
            ];
        });
    }

    public function forContract(Contract $contract): self
    {
        return $this->state(function ($attributes) use ($contract) {
            $lastSalary = $contract->salaries()->orderBy('end_date', 'desc')->first();
            return [
                'contract_id' => $contract->id,
                'price' => $contract->salary_price,
                'currency' => $contract->salary_currency,
                'type' => $contract->salary_type,
                'start_date' => $startDate = $lastSalary?->start_date->clone()->addMinute() ?: $contract->start_date->clone(),
                'end_date' => $endDate = match ($contract->salary_type) {
                    SalaryTypeEnum::ByMonth => $startDate->clone()->addMonth(),
                    default => $startDate->clone()->addDay(),
                },
                'worked_days' => $workedDays = $startDate->diffInDays($endDate),
                'worked_hours' => $workedDays * $contract->day_work_hours,
            ];
        });
    }
}

