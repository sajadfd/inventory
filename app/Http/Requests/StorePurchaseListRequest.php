<?php

namespace App\Http\Requests;

use App\Rules\DateParameterFormatterRule;
use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePurchaseListRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'date' => [Rule::requiredIf(!$this->purchase_list), new DateParameterFormatterRule()],
            'supplier_id' => [Rule::requiredIf(!$this->purchase_list), Rule::exists('suppliers', 'id')],
            'notes' => 'nullable|string'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->purchase_list?->is_confirmed) {
                $validator->errors()->add('general', __('List is confirmed, cannot modify'));
            }
        });
    }

}
