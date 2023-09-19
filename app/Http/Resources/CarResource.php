<?php

namespace App\Http\Resources;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Car $model */
        $model = $this->resource;
        $model->loadMissing(['customer', 'color', 'carModel', 'carType']);
        $array = $model->toArray();

        if (isset($array['customer'])) {
            $array['customer'] = CustomerResource::make($model->customer);
        }
        if (isset($array['car_type'])) {
            $array['car_type'] = CarTypeResource::make($model->carType);
        }
        if (isset($array['car_model'])) {
            $array['car_model'] = CarModelResource::make($model->carModel);
        }
        if (isset($array['color'])) {
            $array['color'] = ColorResource::make($model->color);
        }
        return $array;
    }
}
