<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\ProductTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_PRODUCT_TRANSACTIONS);
    }

    public function view(User $user, ProductTransaction $productTransaction): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::VIEW_PRODUCT_TRANSACTIONS);
    }
}
