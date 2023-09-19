<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\PurchaseItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseItemPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PURCHASE_LISTS);
    }


    public function view(User $user, PurchaseItem $purchaseItem): bool
    {
        return $user->hasAnyPermission([
            PermissionEnum::VIEW_PURCHASE_LISTS,
            PermissionEnum::CREATE_PURCHASE_ITEMS,
            PermissionEnum::UPDATE_PURCHASE_ITEMS
        ]);

    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_PURCHASE_ITEMS);
    }

    public function update(User $user, PurchaseItem $purchaseItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_PURCHASE_ITEMS);
    }


    public function delete(User $user, PurchaseItem $purchaseItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_PURCHASE_ITEMS);
    }

}
