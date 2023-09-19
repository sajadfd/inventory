<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductUnitResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'product' => ProductResource::make($this->whenLoaded('product'))
        ];
    }
}
