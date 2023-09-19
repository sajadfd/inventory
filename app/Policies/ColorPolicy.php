<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Color;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ColorPolicy
{

    public function viewAny(User $user): bool
    {
       return  $user->hasPermissionTo(PermissionEnum::VIEW_COLORS);
    }

    public function view(User $user, Color $color): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_COLORS);
    }

    public function create(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::CREATE_COLORS);
    }

    public function update(User $user, Color $color): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::UPDATE_COLORS);
    }

    public function delete(User $user, Color $color): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::DELETE_COLORS);
    }

}
