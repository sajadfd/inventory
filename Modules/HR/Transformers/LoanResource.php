<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class LoanResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'contract' => ContractResource::make($this->whenLoaded('contract')),
            'installments' => InstallmentResource::collection($this->whenLoaded('installments')),
        ];

    }
}
