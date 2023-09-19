<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Mpdf\Tag\U;

class CartPolicy
{
    public function updateCurrent(User $user): bool
    {
        return $user->type === UserType::Customer && $user->customer()->exists();
    }

    public function showCurrent(User $user): bool
    {
        return $user->type === UserType::Customer && $user->customer()->exists();
    }

    public function getCartByUser(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_USER_CARTS);
    }

    public function confirm(User $user): bool
    {
        return $user->type === UserType::Customer && $user->customer()->exists();
    }

}
