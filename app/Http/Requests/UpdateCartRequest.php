<?php

namespace App\Http\Requests;

use App\Enums\CartTypeEnum;
use App\Enums\SaleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCartRequest extends FormRequest
{
    public function rules()
    {

        return [
            'notes' => ['string'],
            'type' => ['string', Rule::in(CartTypeEnum::getAllValues())],
            'car_id' => [Rule::requiredIf(fn() => request('type') === SaleType::InventorySale->value), Rule::excludeIf(request('type') === SaleType::StoreSale->value), 'required_with:diagnosis_id', 'numeric', Rule::exists('cars', 'id')->where('customer_id', Auth::user()->customer?->id)],
            'diagnosis_id' => [Rule::requiredIf(fn() => request('type') === SaleType::InventorySale->value), Rule::excludeIf(request('type') === SaleType::StoreSale->value), 'required_with:car_id', 'numeric', 'exists:diagnoses,id'],
        ];
    }

}
