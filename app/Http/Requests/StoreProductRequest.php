<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo(PermissionEnum::CREATE_PRODUCTS);
    }

    public function rules(): array
    {
        $isRequired = !$this->product;

        return [
                'name' => [Rule::requiredIf($isRequired), 'string', Rule::unique('products', 'name')->ignore($this->product?->id)],
                'category_id' => [Rule::requiredIf($isRequired), 'exists:categories,id'],
                'image' => [Rule::requiredIf($isRequired), new ImageOrValidSrcString],
                'initial_purchase_price' => [Rule::requiredIf((!$this->product && request()->has('store'))),
                    'numeric',
                    Rule::excludeIf(+$this->get('initial_purchase_price') === $this->product?->initialStore?->price),
                    Rule::when($this->product, [function ($attr, $val, $fail) {
                        if (!$this->product->initialStore) {
                            $fail(__('Product has no initial store'));
                        } else if ($this->product->initialStore->used) {
                            $fail(__('Product initial store has been used'));
                        }
                    }])],
                'sale_price' => [Rule::requiredIf($isRequired), 'numeric'],
                'depletion_alert_at' => 'numeric|gte:-1',
                'brand_id' => 'nullable|exists:brands,id',
                'product_location_id' => 'nullable|exists:product_locations,id',
                'main_unit_name' => 'nullable|string',
                'barcode' => ['nullable', Rule::unique('products', 'barcode')->ignore($this->product?->id)],
                'notes' => 'nullable|string',
                'is_visible_in_store' => 'boolean',
                'is_active' => 'boolean',
                'source' => 'string|in:inside,outside'
            ] + (!$this->product ? [
                'store' => 'integer|gt:0',
            ] : []);
    }

}
