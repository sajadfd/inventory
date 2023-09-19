<?php

namespace App\Policies;

use App\Enums\OrderStatusEnum;
use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CUSTOMERS_ORDERS) || $user->type === UserType::Customer || $user->type === UserType::Driver;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_CUSTOMERS_ORDERS) || $user->customer?->id === $order->customer_id
            || $user->driver?->id === $order->driver_id;
    }

    public function confirm(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CONFIRM_CUSTOMER_ORDERS) && $order->status === OrderStatusEnum::ConfirmedByCustomer;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->status !== OrderStatusEnum::Finished &&
            ($user->hasPermissionTo(PermissionEnum::CANCEL_CUSTOMERS_ORDERS)
                || ($order->customer_id === $user->customer?->id && $order->status === OrderStatusEnum::ConfirmedByCustomer));
    }

    public function finish(User $user, Order $order): bool
    {
        return $user->driver?->id === $order->driver_id || $user->hasPermissionTo(PermissionEnum::FINISH_ORDERS);
    }
}
