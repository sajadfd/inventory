<?php

namespace App\Http\Requests;

use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => [Rule::requiredIf(!$this->customer), 'string', Rule::unique('customers', 'name')->ignore($this->customer?->id)],
            'phone' => 'nullable',
            'address' => 'nullable|string',
            'image' => ['nullable', new ImageOrValidSrcString],
            'is_active' => 'nullable|boolean',
        ];
    }
}
