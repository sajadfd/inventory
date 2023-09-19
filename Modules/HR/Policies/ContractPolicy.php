<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Contract;
use Modules\HR\Enums\HRPermissionEnum;

class ContractPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_CONTRACTS);
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_CONTRACTS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_CONTRACTS);
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_CONTRACTS);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_CONTRACTS);
    }

    public function deactivate(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_CONTRACTS);
    }

    public function previewDues(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_SALARIES);
    }

}
