<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CUSTOMERS);
    }


    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CUSTOMERS) || $customer->id === $user->customer?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_CUSTOMERS);
    }

    public function update(User $user, Customer $customer): bool
    {
        return ($user->hasPermissionTo(PermissionEnum::UPDATE_CUSTOMERS) && !$customer->user()->exists()) || $customer->user_id === $user->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_CUSTOMERS);
    }

}
