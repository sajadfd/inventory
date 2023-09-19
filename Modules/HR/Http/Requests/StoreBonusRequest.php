<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\HR\Entities\Bonus;
use Modules\HR\Entities\Contract;

/**
 * @property Bonus|null $bonus;
 */
class StoreBonusRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'contract_id' => ['required', Rule::exists('contracts', 'id')->where('is_active', true)->where(fn($q) => $q->where('end_date', '>=', now()))],
            'notes' => ['required', 'string'],
            'price' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date', 'after_or_equal:now', function ($attribute, $value, $fail) {
                $dateInsideExistingSalary = Contract::where('id', request('contract_id'))->whereHas('salaries', function ($q) use ($value) {
                    $q->where('end_date', '>=', $value);
                })->exists();
                if ($dateInsideExistingSalary) {
                    $fail(__('There is salary containing this date'));
                }
                if ($this->bonus?->salary()->exists()) {
                    $fail(__('There is salary using this bonus'));
                }
                $dateOutsideContract = Contract::where('id', request('contract_id'))
                    ->where(function ($query) use ($value) {
                        $query->where('start_date', '>', $value)
                            ->orWhere('end_date', '<', $value);
                    })->exists();
                if ($dateOutsideContract) {
                    $fail(__(key: 'Date outside contract date range'));
                }
            }]];
    }


}
