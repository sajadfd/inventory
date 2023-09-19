<?php

namespace App\Http\Resources;

use App\Models\Supplier;
use App\Services\GenerateImagesFullUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Supplier $model */
        $model = $this->resource;

        GenerateImagesFullUrls::Generate($model);

        $array = $model->toArray();

        return $array;
    }
}
