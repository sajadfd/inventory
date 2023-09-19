<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BrandPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_BRANDS);
    }


    public function view(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_BRANDS);

    }


    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_BRANDS);

    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_BRANDS);

    }


    public function delete(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_BRANDS);

    }

}
