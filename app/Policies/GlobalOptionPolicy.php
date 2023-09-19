<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\GlobalOption;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GlobalOptionPolicy
{

    public function viewAny(User $user): bool
    {
        return  true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GlobalOption $globalOption): bool
    {
        return  true;
    }

    public function update(User $user, GlobalOption $globalOption): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::UPDATE_GLOBAL_OPTIONS);
    }

}
