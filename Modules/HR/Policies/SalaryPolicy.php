<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Salary;
use Modules\HR\Enums\HRPermissionEnum;

class SalaryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_SALARIES);
    }

    public function view(User $user, Salary $salary): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_SALARIES);
    }

    public function delete(User $user, Salary $salary): bool
    {
        return  $user->hasPermissionTo(HRPermissionEnum::DELETE_SALARIES);
    }

    public function pay(User $user, Salary $salary): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::PAY_SALARIES);
    }
    public function merge(User $user, Salary $salary): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MERGE_SALARIES);
    }

    public function calculate(?User $user): bool
    {
        return true;
    }

}
