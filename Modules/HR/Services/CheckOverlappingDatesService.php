<?php
declare(strict_types=1);

namespace Modules\HR\Services;

use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Modules\HR\Entities\Absence;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Entities\OffWeekDay;

class CheckOverlappingDatesService
{
    public static function ContractDatesOverlapping($contract, $startDate, $endDate, $type, $id, $model): string|bool
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        if ($model?->salaries()->exists()) {
            return __("Record has salaries, cannot be updated");
        }

        if ($period->start < $contract->start_date || $period->end > $contract->end_date) {
            //Out of contract range
            return __('Date is out of contract date range ');
        }

        if ($period->start->floatDiffInRealHours($period->end) < 0.5) {
            return __('Date should contain at least half an hour');
        }

        $startEndArray = [$period->start, $period->end];
        $weekDays = [];
        foreach ($period as $date) {
            if (!in_array($date->weekday(), $weekDays)) {
                $weekDays[] = $date->weekday();
            }
        }

        $overlappingAbsenceExists = Absence::query()->where('contract_id', $contract->id)
            ->when($type === Absence::class, function ($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->where(function ($query) use ($period, $startEndArray) {
                $query->whereBetween('start_date', $startEndArray)
                    ->orWhereBetween('end_date', $startEndArray)
                    ->orWhere(function ($q2) use ($period) {
                        $q2->where('start_date', '<', $period->start)->where('end_date', '>', $period->end);
                    });
            })->exists();

        $overlappingAttendanceExists = Attendance::query()->where('contract_id', $contract->id)
            ->when($type === Attendance::class, function ($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->where(function ($query) use ($period, $startEndArray) {
                $query->whereBetween('start_date', $startEndArray)
                    ->orWhereBetween('end_date', $startEndArray)
                    ->orWhere(function ($q2) use ($period) {
                        $q2->where('start_date', '<', $period->start)->where('end_date', '>', $period->end);
                    });
            })->exists();

        $overlappingOffDateExists = OffDate::query()->where('contract_id', $contract->id)
            ->when($type === OffDate::class, function ($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->where(function ($query) use ($period, $startEndArray) {
                $query->whereBetween('start_date', $startEndArray)
                    ->orWhereBetween('end_date', $startEndArray)
                    ->orWhere(function ($q2) use ($period) {
                        $q2->where('start_date', '<', $period->start)->where('end_date', '>', $period->end);
                    });
            })->exists();

        $overlappingOffWeekDayExists = OffWeekDay::query()->where('contract_id', $contract->id)
            ->whereIn('day', $weekDays)->exists();


        if (
            $overlappingAbsenceExists ||
            $overlappingAttendanceExists ||
            $overlappingOffDateExists ||
            $overlappingOffWeekDayExists
        ) {
            return __('There is previous record overlapping this date range');
        }
        return false;
    }

    public static function ContractDatesOverlappingWeekDay(Contract $contract, $day, $model=null): string|bool
    {
        if ($model?->salaries()->exists()) {
            return __("Record has salaries, cannot be updated");
        }

        $collection = $contract->absences->merge($contract->attendances)->merge($contract->offDates);
        foreach ($collection as $item) {
            $period = CarbonPeriod::create($item->start_date, $item->end_date);
            $period->addFilter(function (Carbon $date) use ($day) {
                return $date->weekday() === $day;
            });
            if ($period->count() > 0) {
                return __('There is previous record overlapping this date range');
            }
        }
        return false;
    }

    public static function GetCleanDatePeriod(Contract $contract, ?Carbon $start_date = null, $tries = 0): array
    {
        if ($start_date === null) {
            $start_date = Carbon::make(fake()->unique()->dateTimeBetween($contract->start_date, min($contract->end_date, now()), 'Asia/Baghdad'));
        }
        if ($tries > 50) {
            throw new \Exception("Infinite loop");
        }
        $end_date = $start_date->clone()->add(CarbonInterval::hours($contract->day_work_hours));
        try {
            $targetPeriod = CarbonPeriod::create($start_date, $end_date);
            $weekDays = $contract->offWeekDays->pluck('day')->unique()->toArray();
            $collection = $contract->absences->merge($contract->offDates)->merge($contract->attendances);
            $collection->each(function ($item) use ($contract, $weekDays, &$start_date, $targetPeriod) {
                $period = CarbonPeriod::create($item->start_date, $item->end_date);
                if ($period->contains($targetPeriod) || in_array($start_date->weekday(), $weekDays)) {
                    if ($start_date < $contract->end_date) {
                        $start_date = $item->end_date->clone()->addDay();
                    } else {
                        $start_date = $contract->start_date->clone();
                    }
                    throw new \Exception('overlapping period');
                }
            });
            return [$start_date, $end_date];
        } catch (\Exception $exception) {
            return static::GetCleanDatePeriod($contract, $start_date, $tries++);
        }
    }
}
