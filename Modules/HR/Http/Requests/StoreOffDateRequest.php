<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Services\CheckOverlappingDatesService;

/**
 * @property OffDate|null $off_date
 */
class StoreOffDateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "contract_id" => ['required', Rule::exists('contracts', 'id')->where('is_active', true)->where(fn($q) => $q->where('end_date', '>=', now()))],
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
                if ($this->off_date?->salaries()->exists()) {
                    $validator->errors()->add(
                        'general',
                        __('Record has salaries, cannot be updated')
                    );
                }
                $isOverlapping = CheckOverlappingDatesService::ContractDatesOverlapping(
                    Contract::find(request('contract_id')),
                    request('start_date'),
                    request('end_date'),
                    OffDate::class,
                    $this->route('off_date')?->id,
                    $this->route('off_date'),
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
