<?php

namespace App\Http\Requests;

use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::unique('suppliers', 'name')->ignore($this->supplier?->id)],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'image' => ['nullable', new ImageOrValidSrcString],
            'is_active' => 'nullable|boolean',
        ];
    }
}
