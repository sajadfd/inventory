<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Services\CheckOverlappingDatesService;

class StoreManyOffDateRequest extends FormRequest
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
            "start_date" => ['required', 'date'],
            "end_date" => ['required', 'date'],
            "notes" => ['string'],
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                $contracts = Contract::query()->when(
                    !empty($this->input('contract_ids')),
                    fn(Builder $query) => $query->whereIn('id', $this->input('contract_ids'))
                )->with(['absences', 'attendances', 'offDates', 'offWeekDays'])->get();
                foreach ($contracts as $contract) {
                    $isOverlapping = CheckOverlappingDatesService::ContractDatesOverlapping(
                        $contract,
                        request('start_date'),
                        request('end_date'),
                        OffDate::class,
                        null,
                        null,
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
