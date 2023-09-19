<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SERVICE_ITEMS);
    }

    public function view(User $user, ServiceItem $serviceItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SERVICE_ITEMS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_SERVICE_ITEMS);
    }

    public function update(User $user, ServiceItem $serviceItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_SERVICE_ITEMS);
    }

    public function delete(User $user, ServiceItem $serviceItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_SERVICE_ITEMS);
    }
}
