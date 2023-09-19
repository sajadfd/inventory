<?php

namespace Modules\HR\Transformers;

use Carbon\Carbon;
use JetBrains\PhpStorm\ExpectedValues;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Salary;
use Modules\HR\Services\SalariesService;

class SalaryDataObject
{

    /**
     * @param array<int,float> $penaltiesIds
     * @param int[] $bonusesIds
     * @param array<int,float> $absencesIds
     * @param array<int,float> $attendancesIds
     * @param array<int,float> $offDatesIds
     * @param array<int,int> $offWeekDaysIds
     */
    public function __construct(
        public readonly Contract $contract,
        public readonly float    $price,
        public readonly Carbon   $startDate,
        public readonly Carbon   $endDate,
        public readonly array    $penaltiesIds,
        public readonly array    $bonusesIds,
        public readonly array    $absencesIds,
        public readonly array    $attendancesIds,
        public readonly array    $offDatesIds,
        public readonly array    $offWeekDaysIds,
        public readonly float    $workedDays,
        public readonly float    $workedHours,
    )
    {

    }

    public function store(): Salary
    {
        return SalariesService::StoreSalaryObjectInDatabase($this);
    }


    public function convertRelationToPivotArray(
        #[ExpectedValues(['penaltiesIds', 'bonusesIds', 'absencesIds', 'attendancesIds', 'offDatesIds', 'offWeekDaysIds'])]
        string $relation,
        #[ExpectedValues(['hours', 'price', 'days'])]
        string $pivotColumnName): array
    {

        $carry = [];
        foreach ($this->{$relation} as $id => $count) {
            if ($count > 0) {
                $carry[$id] = [$pivotColumnName => $count];
            }
        }
        return $carry;
    }
}
