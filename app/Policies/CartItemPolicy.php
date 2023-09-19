<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CartItemPolicy
{

    public function create(User $user): bool
    {
        return ($user->type === UserType::Customer) || $user->hasPermissionTo(PermissionEnum::CREATE_USER_CARTS_ITEMS);
    }

    public function update(User $user, CartItem $cartItem): bool
    {
        return $cartItem->cart->user_id === $user->id || $user->hasPermissionTo(PermissionEnum::UPDATE_USER_CARTS_ITEMS);
    }


    public function delete(User $user, CartItem $cartItem): bool
    {
        return $cartItem->cart->user_id === $user->id || $user->hasPermissionTo(PermissionEnum::DELETE_USER_CARTS_ITEMS);
    }


}
