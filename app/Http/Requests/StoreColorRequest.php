<?php

namespace App\Http\Requests;

use App\Http\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreColorRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [Rule::requiredIf(!$this->color), 'string', Rule::unique('colors', 'name')->ignore($this->color?->id)],
            'code' => [Rule::requiredIf(!$this->color), 'string', Rule::unique('colors', 'code')->ignore($this->color?->id)],
            'is_active' => 'boolean',
        ];
    }

}
