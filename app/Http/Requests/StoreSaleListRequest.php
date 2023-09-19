<?php

namespace App\Http\Requests;

use App\Enums\SaleType;
use App\Models\Car;
use App\Models\SaleList;
use App\Rules\DateParameterFormatterRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
/**
 * @property SaleList $saleList
 */
class StoreSaleListRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var SaleList $saleList */
        $saleList = $this->sale_list;
        $isInventory = $this->request->get('type') === 'inventory_sale';
        return [
            'customer_id' => [Rule::requiredIf(!$this->sale_list), 'exists:customers,id'],
            'date' => [Rule::requiredIf(!$this->sale_list), new DateParameterFormatterRule()],
            'type' => [Rule::requiredIf(!$this->sale_list), 'in:store_sale,inventory_sale', function ($attr, string $val, $fail) use ($saleList) {
                if ($saleList?->type->value !== $val && $saleList?->total_price > 0) {
                    $fail(__('Cannot change list type unless its empty'));
                }
            }],
            'car_id' => Rule::when($isInventory, [Rule::requiredIf(!$this->sale_list),
                Rule::exists('cars', 'id')
                    ->where('customer_id', $this->request->get('customer_id'))
            ]),
            'diagnosis_id' => [Rule::when($isInventory, [Rule::requiredIf(!$this->sale_list), 'exists:diagnoses,id'])],
            'mechanic_id' => [Rule::when($isInventory, [Rule::requiredIf(!$this->sale_list), 'exists:mechanics,id'])],
            'notes' => 'nullable|string',
        ];
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->sale_list?->is_confirmed) {
                $validator->errors()->add('general', __('List is confirmed, cannot modify'));
            }
        });
    }

}
