<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Loan;
use Modules\HR\Enums\HRPermissionEnum;

class LoanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_LOANS);
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_LOANS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_LOANS);
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_LOANS);
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_LOANS);
    }
}
