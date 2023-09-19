<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DiagnosisPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_DIAGNOSES) || $user->type === UserType::Customer;
    }

    public function view(User $user, Diagnosis $diagnosis): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_DIAGNOSES)  || $user->type === UserType::Customer;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_DIAGNOSES);
    }

    public function update(User $user, Diagnosis $diagnosis): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_DIAGNOSES);
    }

    public function delete(User $user, Diagnosis $diagnosis): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_DIAGNOSES);
    }

}
