<?php
declare(strict_types=1);

namespace Modules\HR\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\HR\Entities\Absence;
use Modules\HR\Entities\Contract;
use Modules\HR\Services\CheckOverlappingDatesService;

/**
 * @property Absence|null $absence;
 */
class StoreAbsenceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "contract_id" => ['required', Rule::exists('contracts', 'id')->where('is_active', true)->where(fn($q) => $q->where('end_date', '>=', now()))],
            "start_date" => ['required', 'date', 'before:tomorrow'],
            "end_date" => ['required', 'date', 'after:start_date'],
            "notes" => ['string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->absence?->salaries()->exists()) {
                    $validator->errors()->add(
                        'general',
                        __('Record has salaries, cannot be updated')
                    );
                }
                $isOverlapping = CheckOverlappingDatesService::ContractDatesOverlapping(
                    Contract::find(request('contract_id')),
                    request('start_date'),
                    request('end_date'),
                    Absence::class,
                    $this->route('absence')?->id,
                    $this->route('absence'),
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
