<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $logged_user): bool
    {
        return $logged_user->hasPermissionTo(PermissionEnum::VIEW_USERS);
    }

    public function view(User $logged_user, User $user): bool
    {
        return $logged_user->hasPermissionTo(PermissionEnum::VIEW_USERS);
    }

    public function update(User $logged_user, User $user): bool
    {
        return $logged_user->hasPermissionTo(PermissionEnum::UPDATE_USERS);
    }

    public function getAllPermissions(User $logged_user): bool
    {
        return $logged_user->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS);
    }

    public function getUserPermissions(User $logged_user, User $user): bool
    {
        return $logged_user->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS);
    }

    public function givePermissionTo(User $logged_user, User $user): bool
    {
        return $logged_user->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS);
    }
}
