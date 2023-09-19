<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\CarType;
use App\Models\User;

class CarTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CAR_TYPES);
    }

    public function view(User $user, CarType $carType): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CAR_TYPES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_CAR_TYPES);
    }

    public function update(User $user, CarType $carType): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_CAR_TYPES);

    }

    public function delete(User $user, CarType $carType): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_CAR_TYPES);
    }
}
