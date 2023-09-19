<?php

namespace Modules\HR\Services;

use Carbon\Carbon;
use Modules\HR\Entities\Penalty;
use Modules\HR\Entities\Installment;

class PenaltyService
{
    public function createPenaltyAndUpdateInstallment(Installment $installment)
    {
        $penalty = Penalty::create([
            'contract_id' => $installment->loan->contract_id,
            'currency' =>   $installment->loan->currency,
            'currency_value' => $installment->loan->currency_value,
            'price' => $installment->price,
            'date' => Carbon::now(),
            'notes' => 'تسديد قسط رقم  (' . $installment->sequence_number . ') للموظف (' . $installment->loan->contract->employer->name . ')'
        ]);
        $installment->where('id', $installment->id)->update(['penalty_id' => $penalty->id,  'due_date' => $installment->due_date]);
    }
}
