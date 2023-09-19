<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Services\ProductStoreService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @property ?SaleItem $sale_item
 */
class StoreSaleItemRequest extends FormRequest
{
    public ?Product $product = null;
    public ?ProductUnit $productUnit = null;

    public function rules(): array
    {

        $rules = [];
        switch ($this->getMethod()) {
            case "POST":
                $rules = array_merge($rules, [
                    'sale_list_id' => 'required|exists:sale_lists,id',
                    'product_id' => ['required', function ($attr, $val, $fail) {
                        if (!$this->product) {
                            $this->product = Product::where('id', $val)->first();
                        }
                        if (!$this->product) {
                            $fail(__('validation.exists', [__('validation.attributes.product_id')]));
                        }
                    }],
                    'product_unit_id' => [Rule::exists('product_units', 'id')->where('product_id', $this->input('product_id', $this->sale_item?->product_id))],
                ]);
                $this->productUnit = $this->input('product_unit_id') ? ProductUnit::find($this->input('product_unit_id')) : null;
                break;
            case "PUT":
            case "PATCH":
                $this->product = $this->sale_item->product;
                $this->productUnit = $this->sale_item->productUnit;
                break;
        }
        $rules['count'] = [
            'required', 'integer', 'gt:0',
            function ($attr, $val, $fail) {
                if (($this->productUnit?->store ?: $this->product?->store) + ($this->sale_item?->net_count) < $val) {
                    $fail(__("Product store is not enough"));
                }
            }];
        $rules['price'] = [
            Rule::excludeIf(!auth()->user()->hasPermissionTo(PermissionEnum::MODIFY_SALE_ITEMS_PRICES)),
            'numeric',
            function ($attr, $val, $fail) {
                if (!$this->product) {
                    $fail(__('Product is required'));
                }
                if (($purchasePrice = $this->product->latest_purchase_price_in_iqd) > $val) {
                    $fail(__('Price must be greater than purchase price :price', ['price' => number_format($purchasePrice) . ' ' . __('iqd')]));
                }
            }];
        return $rules;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $saleList = match ($this->getMethod()) {
                "POST" => SaleList::query()->find($this->request->get('sale_list_id')),
                "PUT", "PATCH", "DELETE" => $this->sale_item->saleList,
            };
            if ($saleList?->is_confirmed) {
                $validator->errors()->add('general',
                    $this->sale_item ? __("List is confirmed, cannot modify") : __("List is confirmed, cannot add"));
            }
        });
    }

}
