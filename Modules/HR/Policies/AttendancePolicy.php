<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Attendance;
use Modules\HR\Enums\HRPermissionEnum;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_ATTENDANCES);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_ATTENDANCES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_ATTENDANCES);
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_ATTENDANCES) && $attendance->salaries()->doesntExist();
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_ATTENDANCES) && $attendance->salaries()->doesntExist();
    }
}
