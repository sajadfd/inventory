<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Services\GenerateImagesFullUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Order $model */
        $model = $this->resource;

        GenerateImagesFullUrls::Generate($model);

        return [
            ...$model->toArray(),
            'diagnosis' => $this->whenLoaded('diagnosis', fn() => DiagnosisResource::make($this->diagnosis)),
            'car' => $this->whenLoaded('car', fn() => CarResource::make($this->car)),
            'driver' => $this->whenLoaded('driver', fn() => DriverResource::make($this->driver)),
            'customer' => $this->whenLoaded('customer', fn() => CustomerResource::make($this->customer)),
            'sale_list' => $this->whenLoaded('saleList', fn() => new SaleListResource($this->saleList, false)),
            'order_items' => $this->whenLoaded('orderItems', fn() => OrderItemResource::collection($this->orderItems)),
            'status' => [
                'value' => $model->status->value,
                'title' => __($model->status->value),
            ],
        ];
    }
}
