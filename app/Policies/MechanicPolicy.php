<?php
declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Mechanic;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MechanicPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_MECHANIC);
    }

    public function view(User $user, Mechanic $mechanic): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_MECHANIC);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_MECHANIC);

    }

    public function update(User $user, Mechanic $mechanic): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_MECHANIC);

    }

    public function delete(User $user, Mechanic $mechanic): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_MECHANIC);
    }
}
