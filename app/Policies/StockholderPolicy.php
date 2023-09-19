<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Stockholder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockholderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_STOCKHOLDERS);
    }

    public function view(User $user, Stockholder $stockholder): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_STOCKHOLDERS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_STOCKHOLDERS);
    }

    public function update(User $user, Stockholder $stockholder): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_STOCKHOLDERS);
    }

    public function delete(User $user, Stockholder $stockholder): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_STOCKHOLDERS);
    }
}
