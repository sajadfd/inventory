<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Contract;
use Modules\HR\Services\CheckOverlappingDatesService;

/**
 * @property Attendance|null $attendance
 */
class StoreAttendanceRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "contract_id" => ['required', Rule::exists('contracts', 'id')],
            "start_date" => ['required', 'date'],
            "end_date" => ['required', 'date'],
            "notes" => ['string']
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->attendance?->salaries()->exists()) {
                    $validator->errors()->add(
                        'general',
                        __('Record has salaries, cannot be updated')
                    );
                }
                $isOverlapping = CheckOverlappingDatesService::ContractDatesOverlapping(
                    Contract::find(request('contract_id')),
                    request('start_date'),
                    request('end_date'),
                    Attendance::class,
                    $this->route('attendance')?->id,
                    $this->route('absence')
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
