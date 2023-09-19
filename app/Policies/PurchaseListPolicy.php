<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\PurchaseList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseListPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PURCHASE_LISTS);
    }

    public function view(User $user, PurchaseList $purchaseList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PURCHASE_LISTS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_PURCHASE_LISTS);
    }

    public function update(User $user, PurchaseList $purchaseList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_PURCHASE_LISTS);
    }

    public function delete(User $user, PurchaseList $purchaseList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_PURCHASE_LISTS);
    }

    public function confirm(User $user, PurchaseList $purchaseList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CONFIRM_PURCHASE_LISTS);
    }

    public function unConfirm(User $user, PurchaseList $purchaseList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UN_CONFIRM_PURCHASE_LISTS);
    }

    public function autoPay(User $user, PurchaseList $purchaseList): bool
    {
        return $user->hasPermissionTo(PermissionEnum::AUTO_PAY_PURCHASE_LISTS);
    }

}
