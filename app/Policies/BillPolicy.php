<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BillPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_BILLS);
    }


    public function view(User $user, Bill $bill): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_BILLS);
    }

    public function bill(User $user, Bill $bill): bool
    {
        return $user->hasPermissionTo(PermissionEnum::PAY_BILLS);
    }

}
