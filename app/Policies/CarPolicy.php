<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Car;
use App\Models\User;

class CarPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CARS) || $user->type === UserType::Customer;
    }

    public function view(User $user, Car $car): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CARS) || $user->customer?->id === $car->customer_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_CARS) ||  $user->type === UserType::Customer;
    }

    public function update(User $user, Car $car): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_CARS) || $user->customer?->id === $car->customer_id;
    }

    public function delete(User $user, Car $car): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_CARS) || $user->customer?->id === $car->customer_id;
    }

}
