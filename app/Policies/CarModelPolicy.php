<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\CarModel;
use App\Models\User;

class CarModelPolicy
{

    public function viewAny(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_CAR_MODELS);
    }

    public function view(User $user, CarModel $carModel): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_CAR_MODELS);
    }


    public function create(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::CREATE_CAR_MODELS);
    }

    public function update(User $user, CarModel $carModel): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::UPDATE_CAR_MODELS);
    }

    public function delete(User $user, CarModel $carModel): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::DELETE_CAR_MODELS);

    }
}
