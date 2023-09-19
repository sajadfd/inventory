<?php

namespace App\Http\Requests;

use App\Enums\OrderStatusEnum;
use App\Enums\SaleType;
use App\Models\Order;
use App\Models\PurchaseList;
use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class ConfirmOrderRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->order;
        return [
            'driver_id' => ['exists:drivers,id', function ($value, $attribute, $fail) use ($order) {
                if (!$order->end_address || !$order->end_latitude || !$order->end_longitude) {
                    $fail(__("Delivery location is not correct"));
                }
                if ($order->type === SaleType::InventorySale) {
                    $fail(__("Inventory Order can not be delivered"));
                }
            }],
            'appointment' => ['date'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($this->order->status !==OrderStatusEnum::ConfirmedByCustomer) {
                $validator->errors()->add('general', __('Order confirmed, cannot re confirm'));
            }
        });
    }
}
