<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function __construct($resource, public $withRelations = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        if ($this->withRelations) {
            $this->resource->load('bill');
        }
        return parent::toArray($request);
    }
}
