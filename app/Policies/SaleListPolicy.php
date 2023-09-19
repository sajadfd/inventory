<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\PurchaseList;
use App\Models\SaleList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SaleListPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SALE_LISTS);
    }

    public function view(User $user, SaleList $saleList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SALE_LISTS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_SALE_LISTS);
    }

    public function update(User $user, SaleList $saleList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_SALE_LISTS);
    }

    public function delete(User $user, SaleList $saleList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_SALE_LISTS);
    }


    public function confirm(User $user, SaleList $saleList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CONFIRM_SALE_LISTS);
    }

    public function unConfirm(User $user, SaleList $saleList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UN_CONFIRM_SALE_LISTS);
    }

    public function autoPay(User $user, SaleList $saleList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::AUTO_PAY_SALE_LISTS);
    }

}
