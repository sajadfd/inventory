<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Entities\OffWeekDay;
use Modules\HR\Services\CheckOverlappingDatesService;

/**
 * @property OffWeekDay|null $off_week_day
 */
class StoreOffWeekDayRequest extends FormRequest
{
    public function rules()
    {
        return [
            "contract_id" => ['required', Rule::exists('contracts', 'id')->where('is_active', true)->where(fn($q) => $q->where('end_date', '>=', now()))],
            "consider_as_attendance" => ['boolean'],
            "day" => ['required', 'numeric', 'in:0,1,2,3,4,5,6',
                Rule::unique('off_week_days', 'day')
                    ->where('contract_id', $this->input('contract_id'))
            ],
            "notes" => ["string"]
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                if ($this->off_week_day?->salaries()->exists()) {
                    $validator->errors()->add(
                        'general',
                        __('Record has salaries, cannot be updated')
                    );
                }

                $contract = Contract::find(request('contract_id'));
                $isOverlapping = CheckOverlappingDatesService::ContractDatesOverlappingWeekDay(
                    $contract,
                    $this->input('day'),
                    $this->route('off_week_day'),
                );
                if ($isOverlapping) {
                    $validator->errors()->add(
                        'general',
                        $isOverlapping
                    );
                }
            }
        ];
    }

}
