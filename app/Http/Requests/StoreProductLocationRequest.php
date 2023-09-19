<?php

namespace App\Http\Requests;

use App\Models\ProductLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property ProductLocation|null $product_location
 * */
class StoreProductLocationRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('product_locations', 'name')->ignore($this->product_location?->id)],
            'is_active' => 'boolean'
        ];
    }
}
