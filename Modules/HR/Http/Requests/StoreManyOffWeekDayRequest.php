<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffWeekDay;
use Modules\HR\Services\CheckOverlappingDatesService;

class StoreManyOffWeekDayRequest extends FormRequest
{
    public function rules()
    {
        return [
            "contract_ids" => ['array', function ($attribute, $value, $fail) {
                $contract_ids = Contract::query()->where('is_active', true)->pluck('id')->toArray();
                foreach ($value as $id) {
                    if (!in_array($id, $contract_ids)) {
                        $fail(__('Contract of id :id is not found', ['id' => $id]));
                    }
                }
            }],
            "consider_as_attendance" => ['boolean'],
            "day" => ['required', 'numeric', 'in:0,1,2,3,4,5,6',
                function ($attr, $val, $fail) {
                    if (Contract::query()->when(
                        !empty($this->input('contract_ids')),
                        fn($query) => $query->whereIn('id', $this->input('contract_ids'))
                    )->whereHas('offWeekDays', function ($q) use ($val) {
                        $q->where('day', $val);
                    })->exists()) {
                        $fail(__('This day is exists in some records'));
                    };
                },
            ],
            "notes" => ["string"]
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                $contracts = Contract::query()->when(
                    !empty($this->input('contract_ids')),
                    fn($query) => $query->whereIn('id', $this->input('contract_ids'))
                )->with(['absences', 'attendances', 'offDates', 'offWeekDays'])->get();
                foreach ($contracts as $contract) {
                    $isOverlapping = CheckOverlappingDatesService::ContractDatesOverlappingWeekDay(
                        $contract,
                        $this->input('day')
                    );
                    if ($isOverlapping) {
                        $validator->errors()->add(
                            'general',
                            $isOverlapping
                        );
                        break;
                    }
                }

            }
        ];
    }

}
