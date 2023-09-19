<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (auth()->user()?->type === UserType::Customer && auth()->user()->customer) {
            $this->merge([
                'customer_id' => auth()->user()->customer->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id' => [Rule::requiredIf(!$this->car), 'exists:customers,id'],
            'car_type_id' => [Rule::requiredIf(!$this->car), 'exists:car_types,id'],
            'color_id' => [Rule::requiredIf(!$this->car), 'exists:colors,id'],
            'car_model_id' => [Rule::requiredIf(!$this->car), 'exists:car_models,id'],
            'model_year' => 'nullable|numeric',
            'plate_number' => 'nullable|string',
            'vin' => ['nullable', Rule::unique('cars', 'vin')->ignore($this->car?->id)],
            'meter_number' => 'nullable|numeric',
            'is_active' => 'boolean',
            'notes' => 'nullable|string'
        ];
    }

}
