<?php

namespace App\Http\Resources;

use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SaleItem $model */
        $model = $this->resource;
        return array_merge($model->toArray(), [
            'product' => ProductResource::make($model->product),
            'product_unit' => ProductUnitResource::make($this->whenLoaded('productUnit'))
        ]);
    }
}
