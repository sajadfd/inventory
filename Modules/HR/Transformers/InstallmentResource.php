<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'loan' => LoanResource::make($this->whenLoaded('loan')),
        ];
    }
}
