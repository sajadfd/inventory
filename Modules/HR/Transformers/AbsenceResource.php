<?php

namespace Modules\HR\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'contract' => ContractResource::make($this->whenLoaded('contract')),
            'salaries' => SalaryResource::collection($this->whenLoaded('salaries'))
        ];

    }
}
