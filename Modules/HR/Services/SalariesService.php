<?php

namespace Modules\HR\Services;
ini_set('max_execution_time', 60 * 59);

use Carbon\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ExpectedValues;
use Modules\HR\Entities\Absence;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Bonus;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Entities\OffWeekDay;
use Modules\HR\Entities\Salary;
use Modules\HR\Enums\SalaryTypeEnum;
use Modules\HR\Enums\TrackByEnum;
use Modules\HR\Transformers\SalaryDataObject;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class SalariesService
{

    public static function GetDaySalaryForMonthFromStartDate(Carbon $startDate, Carbon $endDate, $contract): float
    {
        $currentMonthStartDate = $startDate;

        if ($startDate->floatDiffInMonths($endDate) > 1) {
            $daySalaries = collect();
            $newStartDate = $startDate->copy();
            $newEndDate = $endDate->copy();
            while ($newStartDate->lt($newEndDate)) {
                $price = self::GetDaySalaryForMonthFromStartDate($newStartDate, $newStartDate->copy()->addMonth(), $contract);
                $daySalaries->add(['price' => $price]);
                $newStartDate->addMonth();
            }
            return $daySalaries->avg('price');
        } else {
            $initialCurrentMonthEndDate = $startDate->copy()->addMonth();
            $monthDays = $initialCurrentMonthEndDate->diffInDays($currentMonthStartDate);
            return round($contract->salary_price / $monthDays, 5);
        }
    }

    public static function SplitNumber($number, $divisor): array
    {
        $divisions = [];

        while ($number >= $divisor) {
            $divisions[] = $divisor;
            $number -= $divisor;
        }

        if ($number > 0) {
            $divisions[] = $number;
        }

        return $divisions;
    }

    /**
     * @param Collection $salaries
     * @param string $relation
     * @return \Closure|mixed|null
     */

    public static function UnifySalariesRelationWithValues(
        Collection $salaries,
        #[ExpectedValues(['penaltiesIds', 'bonusesIds', 'absencesIds', 'attendancesIds', 'offDatesIds', 'offWeekDaysIds'])]
        string     $relation
    ): array
    {
        return $salaries->reduce(function ($carry, SalaryDataObject $salary) use ($relation) {
            foreach ($salary->{$relation} as $id => $count) {
                if (isset($carry[$id])) {
                    $carry[$id] += $count;
                } else {
                    $carry[$id] = $count;
                }
            }
            return $carry;
        }, []);
    }

    public static function CalculateContractSalaries(Contract $contract, bool $calculateAsOne = false): Collection
    {
        $startDate = $contract->salaries->sortByDesc('end_date')->first()?->end_date->copy() ?: $contract->start_date->copy();
        if ($startDate->hour > $contract->dayWorkEndHour) {
            $startDate->addDay()->setHour($contract->day_work_start_hour);
        }

        $endDate = $contract->end_date;
        $salaries = new Collection();
        $offWeekDaysPluckedByDayToIdPair = $contract->offWeekDays->pluck('id', 'day');
        while ($startDate->lt($endDate)) {
            if ($contract->salary_type === SalaryTypeEnum::ByMonth) {
                //Months
                $localEndDate = min($startDate->clone()->addMonth(), now());
            } else {
                //Days
                $localEndDate = min($startDate->clone()->addDay(), now());
            }
            $diffInDays = $startDate->diffInDays($localEndDate);

            if ($contract->salary_type === SalaryTypeEnum::ByMonth) {
                if ($startDate->diffInMonths($localEndDate) < 1 && $endDate > now()) {
                    break;
                }
                $daySalary = SalariesService::GetDaySalaryForMonthFromStartDate($startDate, $localEndDate, $contract);
            } else {
                if ($startDate->diffInDays($localEndDate) < 1 && $endDate > now()) {
                    break;
                }
                $daySalary = $contract->salary_price;
            }

            $absences_ids = [];
            $attendances_ids = [];
            $offDates_ids = [];
            $offWeekDays_ids = [];
            $salaryPeriodInDays = Period::make($startDate, $localEndDate, Precision::DAY());
            $salaryPeriodInHours = Period::make($startDate, $localEndDate, Precision::HOUR());

            if ($contract->track_by === TrackByEnum::Absences) {
                $dues = $daySalary * $diffInDays;
                $workedDays = $diffInDays;
                $workedHours = $diffInDays * $contract->day_work_hours;

                $offWeekDaysAbsenceHours = 0;
                if ($contract->offWeekDaysNumbersWithoutAttendance) {
                    foreach ($salaryPeriodInDays as $day) {
                        if (in_array($dayNumber = +$day->format('w'), $contract->offWeekDaysNumbersWithoutAttendance)) {
                            $offWeekDaysAbsenceHours += $contract->day_work_hours;
                            if ($offWeekDayId = $offWeekDaysPluckedByDayToIdPair[$dayNumber] ?? null) {
                                $offWeekDays_ids[$offWeekDayId] = 1 + ($offWeekDays_ids[$offWeekDayId] ?? 0);
                            }
                        }
                    }
                }


                $missedDates = $contract->absences
                    ->merge($contract->offDates->where('consider_as_attendance', false))
                    ->where(fn($item) => $item->periodInHours->overlapsWith($salaryPeriodInHours));

                $missedHours = $offWeekDaysAbsenceHours + $missedDates
                        ->sum(function (Absence|OffDate $item) use ($contract, &$absences_ids, &$offDates_ids) {
                            $hours = 0;
                            $minutes = 0;
                            foreach ($item->periodInMinutes as $minuteDate) {
                                if (in_array(+$minuteDate->format('w'), $contract->offWeekDaysNumbersWithoutAttendance)) continue;
                                $minutes++;
                            }

                            $diffInHours = $minutes / 60;
                            $days = SalariesService::SplitNumber($diffInHours, 24);
                            foreach ($days as $dayHours) {
                                $hours += min(round($dayHours), $contract->day_work_hours);
                            }

                            if ($hours > 0) {
                                if (get_class($item) === Absence::class) {
                                    $absences_ids[$item->id] = $hours;
                                } else if (get_class($item) === OffDate::class) {
                                    $offDates_ids[$item->id] = $hours;
                                }
                            }
                            return $hours;
                        });

                $missedDays = $missedHours / $contract->day_work_hours;
                $workedHours -= $missedHours;
                $workedDays -= $missedDays;
                $dues -= round($missedDays * $daySalary, 2);
            } else {
                //By Day
                $offWeekDaysAttendanceHours = 0;
                if ($contract->offWeekDaysNumbersWithAttendance) {
                    foreach ($salaryPeriodInDays as $day) {
                        if (in_array($dayNumber = +$day->format('w'), $contract->offWeekDaysNumbersWithAttendance)) {
                            $offWeekDaysAttendanceHours += $contract->day_work_hours;
                            if ($offWeekDayId = $offWeekDaysPluckedByDayToIdPair[$dayNumber] ?? null) {
                                $offWeekDays_ids[$offWeekDayId] = 1 + ($offWeekDays_ids[$offWeekDayId] ?? 0);
                            }
                        }
                    }
                }

                $attendanceDates = $contract->attendances
                    ->merge($contract->offDates->where('consider_as_attendance', true))
                    ->where(fn(Attendance|OffDate $item) => $item->periodInHours->overlapsWith($salaryPeriodInHours));

                $attendanceHours = $offWeekDaysAttendanceHours + $attendanceDates
                        ->sum(function (Attendance|OffDate $item) use ($contract, &$attendances_ids, &$offDates_ids) {
                            $hours = 0;
                            $minutes = 0;
                            foreach ($item->periodInMinutes as $minuteDate) {
                                if (in_array(+$minuteDate->format('w'), $contract->offWeekDaysNumbersWithAttendance)) continue;
                                $minutes++;
                            }

                            $diffInHours = $minutes / 60;
                            $days = SalariesService::SplitNumber($diffInHours, 24);
                            foreach ($days as $dayHours) {
                                $hours += min(round($dayHours), $contract->day_work_hours);
                            }

                            if ($hours > 0) {
                                if (get_class($item) === Attendance::class) {
                                    $attendances_ids[$item->id] = $hours;
                                } else if (get_class($item) === OffDate::class) {
                                    $offDates_ids[$item->id] = $hours;
                                }
                            }
                            return $hours;
                        });

                $attendanceDays = $attendanceHours / $contract->day_work_hours;
                $workedHours = $attendanceHours;
                $workedDays = $attendanceDays;
                $dues = round($attendanceDays * $daySalary, 2);
            }
            $salaryPrice = $dues;


            $bonusesQuery = $contract->bonuses->where('salary_id', null)->whereBetween('date', [$startDate, $localEndDate]);
            $bonusesPrice = $bonusesQuery->sum('price');
            $bonuses_ids = $bonusesQuery->pluck('id')->toArray();
            $bonusesQuery->each(function (Bonus $bonus) {
                $bonus->salary_id = 'x';
            });

            $salaryPrice += $bonusesPrice;

            $penalties_ids = [];
            $penalties = $contract->penalties->where('date', '<', $localEndDate)->where('remaining_price', '>', 0);


            foreach ($penalties as $penalty) {
                $newPayedPenaltyPrice = min($penalty->remaining_price, round($salaryPrice, 2));
                if ($newPayedPenaltyPrice > 0) {
                    $penalties_ids[$penalty->id] = $newPayedPenaltyPrice;
                    $salaryPrice -= $newPayedPenaltyPrice;
                    $penalty->localPayedPrice = $penalty->payed_price + $newPayedPenaltyPrice;
                }
            }

            $salaryObject = new SalaryDataObject(
                $contract,
                $salaryPrice,
                $startDate,
                $localEndDate,
                $penalties_ids,
                $bonuses_ids,
                $absences_ids,
                $attendances_ids,
                $offDates_ids,
                $offWeekDays_ids,
                $workedDays,
                $workedHours,
            );
            $salaries->add($salaryObject);
            $startDate = $localEndDate;
        }
        if ($calculateAsOne) {

            $unifiedSalaryObject = new SalaryDataObject(
                $contract,
                $salaries->sum('price'),
                $salaries->first()->startDate,
                $salaries->last()->endDate,
                $salaries->pluck('bonusesIds')->flatten()->unique()->toArray(),
                SalariesService::UnifySalariesRelationWithValues($salaries, 'penaltiesIds'),
                SalariesService::UnifySalariesRelationWithValues($salaries, 'absencesIds'),
                SalariesService::UnifySalariesRelationWithValues($salaries, 'attendancesIds'),
                SalariesService::UnifySalariesRelationWithValues($salaries, 'offDatesIds'),
                SalariesService::UnifySalariesRelationWithValues($salaries, 'offWeekDaysIds'),
                $salaries->sum('workedDays'),
                $salaries->sum('workedHours'),
            );
            $salaries = new Collection([$unifiedSalaryObject]);
        }

        return $salaries;
    }

    public static function StoreSalaryObjectInDatabase(SalaryDataObject $salaryObject): Salary
    {
        $contract = $salaryObject->contract;
        /** @var Salary $salary */
        $salary = $contract->salaries()->create([
            'price' => $salaryObject->price,
            'start_date' => $salaryObject->startDate,
            'end_date' => $salaryObject->endDate,
            'due_date' => $salaryObject->endDate,
            'type' => $contract->salary_type->value,
            'currency' => $contract->salary_currency,
            'currency_value' => GlobalOptionsService::GetCurrencyValue(),
            'worked_days' => $salaryObject->workedDays,
            'worked_hours' => $salaryObject->workedHours,
        ]);

        if (!empty($salaryObject->absencesIds)) {
            $salary->absences()->attach($salaryObject->convertRelationToPivotArray('absencesIds', 'hours'));
        }
        if (!empty($salaryObject->attendancesIds)) {
            $salary->attendances()->attach($salaryObject->convertRelationToPivotArray('attendancesIds', 'hours'));
        }
        if (!empty($salaryObject->offDatesIds)) {
            $salary->offDates()->attach($salaryObject->convertRelationToPivotArray('offDatesIds', 'hours'));
        }
        if (!empty($salaryObject->offWeekDaysIds)) {
            $salary->offWeekDays()->attach($salaryObject->convertRelationToPivotArray('offWeekDaysIds', 'days'));
        }
        if (!empty($salaryObject->penaltiesIds)) {
            $salary->penalties()->attach($salaryObject->convertRelationToPivotArray('penaltiesIds', 'price'));
        }
        if (!empty($salaryObject->bonusesIds)) {
            Bonus::query()->findMany($salaryObject->bonusesIds)->each(fn(Bonus $bonus) => $bonus->update(['salary_id' => $salary->id]));
        }

        return $salary;
    }

}
