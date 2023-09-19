<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BonusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'contract' => ContractResource::make($this->whenLoaded('contract')),
            'salary' => SalaryResource::make($this->whenLoaded('salary')),
        ];
    }
}
