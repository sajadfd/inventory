<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockholderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [Rule::requiredIf(!$this->stockholder), Rule::unique('stockholders', 'name')->ignore($this->stockholder?->id)],
            'inventory_stocks' => [Rule::requiredIf(!$this->stockholder), 'numeric', 'gte:0'],
            'store_stocks' => [Rule::requiredIf(!$this->stockholder), 'numeric', 'gte:0'],
        ];
    }
}
