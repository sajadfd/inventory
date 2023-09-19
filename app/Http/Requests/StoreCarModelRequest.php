<?php

namespace App\Http\Requests;

use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [Rule::requiredIf(!$this->car_model), 'string', Rule::unique('car_models', 'name')->ignore($this->car_model?->id)],
            'image' => ['nullable',new ImageOrValidSrcString],
            'is_active' => 'boolean',
        ];
    }
}
