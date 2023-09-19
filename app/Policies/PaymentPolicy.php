<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Payment;
use App\Models\SaleList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PAYMENTS);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_PAYMENTS) || ($payment->bill->billable_type === SaleList::class && $payment->bill->person_id === auth()->user()->customer?->id);
    }
    /*
    public function create(User $user): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::CREATE_PAYMENTS);
    }

    public function update(User $user, Payment $payment): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::UPDATE_PAYMENTS);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return  $user->hasPermissionTo(PermissionEnum::DELETE_PAYMENTS);
    }*/
}
