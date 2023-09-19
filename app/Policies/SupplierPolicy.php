<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupplierPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SUPPLIERS);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SUPPLIERS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_SUPPLIERS);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_SUPPLIERS);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_SUPPLIERS);
    }
}
