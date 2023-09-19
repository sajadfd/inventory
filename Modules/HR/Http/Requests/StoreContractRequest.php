<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\HR\Enums\SalaryCurrencyEnum;
use Modules\HR\Enums\SalaryTypeEnum;
use Modules\HR\Enums\TrackByEnum;

class StoreContractRequest extends FormRequest
{
    public function rules()
    {
        return [
            "employer_id" => ['required', Rule::exists('employers', 'id')->where('is_active', true)],
            "start_date" => ['required', 'date'],
            "end_date" => ['required', 'date'],
            "salary_type" => ['required', Rule::in(SalaryTypeEnum::getAllValues())],
            "salary_price" => ['required', 'numeric', 'gt:0'],
            "salary_currency" => [Rule::in(SalaryCurrencyEnum::getAllValues())],
            "day_work_hours" => ['required', 'numeric', 'gt:0', 'lte:24'],
            "day_work_start_hour" => ['required', 'numeric', 'gte:0', 'lte:24'],
            "track_by" => ['required', Rule::in(TrackByEnum::getAllValues())],
            'notes' => ["string"],
        ];
    }
}
