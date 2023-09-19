<?php

namespace App\Http\Resources;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var Bill $bill */
        $bill = $this->resource;
        $bill->loadMissing('payments');
        $bill->basicAppends();
        return parent::toArray($request);
    }
}
