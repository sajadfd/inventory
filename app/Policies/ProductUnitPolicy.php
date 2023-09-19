<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductUnitPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PRODUCT_UNITS);
    }


    public function view(User $user, ProductUnit $productUnit): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PRODUCT_UNITS);
    }


    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_PRODUCT_UNITS);
    }


    public function update(User $user, ProductUnit $productUnit): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_PRODUCT_UNITS);
    }


    public function delete(User $user, ProductUnit $productUnit): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_PRODUCT_UNITS);
    }

}
