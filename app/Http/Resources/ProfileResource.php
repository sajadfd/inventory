<?php

namespace App\Http\Resources;

use App\Services\GenerateImagesFullUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        GenerateImagesFullUrls::Generate($model);

        return parent::toArray($request);
    }
}
