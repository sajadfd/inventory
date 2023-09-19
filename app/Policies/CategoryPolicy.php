<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Category $category): bool
    {
        return true;
    }


    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_CATEGORIES);
    }


    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_CATEGORIES);
    }


    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_CATEGORIES);
    }


    public function restore(User $user, Category $category): bool
    {
        return false;
    }


    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }
}
