<?php

namespace Modules\HR\Transformers;

use App\Services\GenerateImagesFullUrls;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployerResource extends JsonResource
{
    public function toArray($request)
    {
        $model = $this->resource;

        GenerateImagesFullUrls::Generate($model);

        return $model->toArray();
    }
}
