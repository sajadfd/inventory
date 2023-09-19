<?php
declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_EXPENSES);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_EXPENSES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_EXPENSES);
    }


    public function update(User $user, Expense $expense): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_EXPENSES) && $user->id === $expense->created_by;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_EXPENSES);
    }

}
