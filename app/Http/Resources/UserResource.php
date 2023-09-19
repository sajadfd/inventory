<?php

namespace App\Http\Resources;

use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('profile');

        if ($this->resource->type === UserType::Customer) {
            $this->resource->loadMissing('customer');
        }

        $array = $this->resource->toArray();

        if ($this->resource->relationLoaded('profile')) {
            $array['profile'] = ProfileResource::make($this->resource->profile);
        }
        if ($this->resource->relationLoaded('customer')) {
            $array['customer'] = ProfileResource::make($this->resource->customer);
        }
        if ($this->resource->relationLoaded('permissions')) {
            $array['permissions'] = $this->resource->permissions->pluck('name');
        }
        if ($this->resource->relationLoaded('roles')) {
            $array['roles'] = $this->resource->roles->pluck('name');
        }
        return $array;
    }
}
