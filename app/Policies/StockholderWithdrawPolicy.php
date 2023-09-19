<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\StockholderWithdraw;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockholderWithdrawPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_STOCKHOLDERWITHDRAWALS);
    }

    public function view(User $user, StockholderWithdraw $stockholderWithdraw): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_STOCKHOLDERWITHDRAWALS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_STOCKHOLDERWITHDRAWALS);
    }

    public function update(User $user, StockholderWithdraw $stockholderWithdraw): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_STOCKHOLDERWITHDRAWALS);
    }

    public function delete(User $user, StockholderWithdraw $stockholderWithdraw): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_STOCKHOLDERWITHDRAWALS);
    }
}
