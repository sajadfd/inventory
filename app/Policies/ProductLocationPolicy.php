<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\ProductLocation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PRODUCT_LOCATIONS);
    }

    public function view(User $user, ProductLocation $productLocation): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PRODUCT_LOCATIONS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_PRODUCT_LOCATIONS);
    }

    public function update(User $user, ProductLocation $productLocation): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_PRODUCT_LOCATIONS);
    }

    public function delete(User $user, ProductLocation $productLocation): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_PRODUCT_LOCATIONS);
    }

}
