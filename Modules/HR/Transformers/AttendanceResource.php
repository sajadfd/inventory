<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'contract' => ContractResource::make($this->whenLoaded('contract')),
            'salaries' => SalaryResource::collection($this->whenLoaded('salaries'))
        ];
    }
}
