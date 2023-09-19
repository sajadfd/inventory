<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        /** @var PurchaseItem $model */
        $model = $this->resource;
        $model->append(['total_price_in_usd', 'total_price_in_iqd']);

        return array_merge($model->toArray(), [
            'product' => ProductResource::make($model->product),
            'product_unit' => ProductUnitResource::make($this->whenLoaded('productUnit'))
        ]);
    }
}
