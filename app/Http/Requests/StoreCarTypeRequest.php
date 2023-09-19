<?php

namespace App\Http\Requests;

use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [Rule::requiredIf(!$this->car_type), 'string', Rule::unique('car_types', 'name')->ignore($this->car_type?->id)],
            'image' => ['nullable', new ImageOrValidSrcString],
            'is_active' => 'boolean',
        ];
    }
}
