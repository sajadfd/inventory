<?php

namespace App\Http\Requests;

use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @property ?PurchaseItem $purchase_item
 */
class StorePurchaseItemRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'price' => [Rule::requiredIf(!$this->purchase_item), 'numeric', 'gte:0'],
            'sale_price' => ['numeric', 'gte:0'],
            'count' => [Rule::requiredIf(!$this->purchase_item), 'integer', 'gt:0'],
            'product_id' => [Rule::requiredIf(!$this->purchase_item), 'numeric', Rule::exists('products', 'id')],
            'product_unit_id' => [Rule::exists('product_units', 'id')->where('product_id', $this->input('product_id', $this->purchase_item?->product_id))],
            'purchase_list_id' => [Rule::requiredIf(!$this->purchase_item), Rule::exists('purchase_lists', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $purchaseList = PurchaseList::query()->find($this->request->get('purchase_list_id', $this->purchase_item?->purchase_list_id));
            if ($purchaseList?->is_confirmed) {
                $validator->errors()->add('general', $this->purchase_item ? __("List is confirmed, cannot modify") : __("List is confirmed, cannot add"));
            }
        });
    }
}
