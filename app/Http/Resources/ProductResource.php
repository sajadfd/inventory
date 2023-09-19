<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\GenerateImagesFullUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        GenerateImagesFullUrls::Generate($product);


        $product->append(['sale_price_in_iqd', 'sale_price_in_usd', 'latest_purchase_price_in_iqd', 'latest_purchase_price_in_usd']);
        $product->makeHidden(['latestPurchaseItem', 'sale_price', 'sale_currency', 'initial_purchase_currency']);

        return array_merge($product->toArray(), [
            'category' => CategoryResource::make($product->category),
            'product_units' => ProductUnitResource::collection($this->whenLoaded('productUnits'))
        ]);
    }
}
