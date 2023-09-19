<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_SERVICES);
    }

    public function view(User $user, Service $service): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_SERVICES);
    }

    public function create(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::CREATE_SERVICES);
    }

    public function update(User $user, Service $service): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::UPDATE_SERVICES);
    }

    public function delete(User $user, Service $service): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::DELETE_SERVICES);
    }
}
