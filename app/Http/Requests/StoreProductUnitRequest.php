<?php

namespace App\Http\Requests;

use App\Enums\ProductUnitType;
use App\Models\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @property ?ProductUnit $product_unit
 */
class StoreProductUnitRequest extends FormRequest
{

    public function rules(): array
    {
        return match ($this->getMethod()) {
            "POST", "PUT", "PATCH" => [
                'product_id' => ['required', Rule::exists('products', 'id')],
                'name' => ['required', Rule::unique('product_units', 'name')->where('is_active', true)->where('product_id', $this->input('product_id'))->ignore($this->product_unit?->id)],
                'type' => ['required', Rule::in(ProductUnitType::getAllValues())],
                'factor' => ['required', 'numeric', 'gt:0', 'lte:100'],
                'price' => ['required', 'numeric', 'gt:0'],
                'notes' => ['nullable','string'],
                'is_active' => 'boolean',
                'is_default' => 'boolean',
                'is_visible_in_store' => 'boolean',
            ],
            default => [],
        };

    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            switch ($this->getMethod()) {
                case "DELETE":
                case "PUT":
                case "PATCH":
                    if ($this->product_unit->sourceTransactions()->exists() || $this->product_unit->targetTransactions()->exists() || $this->product_unit->purchaseItems()->exists() || $this->product_unit->saleItems()->exists()) {
                        $validator->errors()->add('general', __('Unit is used, cannot modify/delete'));
                    }
                    break;
            }
        });
    }
}
