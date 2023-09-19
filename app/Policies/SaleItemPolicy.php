<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SaleItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SALE_ITEMS);
    }

    public function view(User $user, SaleItem $saleItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_SALE_ITEMS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_SALE_ITEMS);
    }

    public function update(User $user, SaleItem $saleItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_SALE_ITEMS);
    }

    public function delete(User $user, SaleItem $saleItem): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_SALE_ITEMS);
    }

}
