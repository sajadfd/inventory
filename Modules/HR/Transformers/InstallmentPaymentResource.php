<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentPaymentResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'installmentPayments' => InstallmentPaymentResource::make($this->whenLoaded('installmentPayments')),
        ];
    }
}
