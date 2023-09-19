<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'employer' => EmployerResource::make($this->whenLoaded('employer'))
        ];
    }
}
