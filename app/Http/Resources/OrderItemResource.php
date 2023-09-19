<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'product_unit' => ProductUnitResource::make($this->whenLoaded('productUnit')),
        ];
    }
}
