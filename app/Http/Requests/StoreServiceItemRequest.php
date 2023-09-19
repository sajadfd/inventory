<?php

namespace App\Http\Requests;

use App\Enums\SaleType;
use App\Models\SaleList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceItemRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $oldId = $this->service_item?->id;
        return [
                'service_id' => [Rule::requiredIf(!$this->service_item), 'exists:services,id'],
                'count' => [Rule::requiredIf(!$this->service_item), 'numeric', 'gt:0'],
                'price' => [Rule::requiredIf(!$this->service_item), 'numeric','gte:0'],
            ] +
            (!$oldId ? [
                'sale_list_id' => ['required', 'exists:sale_lists,id', function ($attr, $val, $fail) {
                    $saleList = SaleList::query()->find($this->request->get('sale_list_id'));
                    if ($saleList?->type !== SaleType::InventorySale) {
                        $fail(__("List must be of inventory sale type"));
                    }
                }],
            ] : []);
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $saleList = $this->service_item?->saleList ?: SaleList::query()->find($this->request->get('sale_list_id'));
            if ($saleList?->is_confirmed) {
                $validator->errors()->add('general', __("List is confirmed, cannot add"));
            }

        });
    }

}
