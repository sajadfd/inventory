<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => [Rule::requiredIf(!$this->service), Rule::unique('services', 'name')->ignore($this->service?->id)],
            'price' => [Rule::requiredIf(!$this->service), 'numeric:gte:0'],
        ];
    }
}
