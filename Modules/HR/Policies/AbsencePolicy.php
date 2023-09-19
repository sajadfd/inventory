<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Absence;
use Modules\HR\Enums\HRPermissionEnum;

class AbsencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_ABSENCES);
    }

    public function view(User $user, Absence $absence): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_ABSENCES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_ABSENCES);
    }

    public function update(User $user, Absence $absence): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_ABSENCES) && $absence->salaries()->doesntExist();
    }

    public function delete(User $user, Absence $absence): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_ABSENCES) && $absence->salaries()->doesntExist();
    }
}
