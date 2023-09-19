<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\OffWeekDay;
use Modules\HR\Enums\HRPermissionEnum;

class OffWeekDayPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_OFF_WEEK_DAYS);
    }

    public function view(User $user, OffWeekDay $offWeekDay): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_OFF_WEEK_DAYS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_OFF_WEEK_DAYS);
    }

    public function update(User $user, OffWeekDay $offWeekDay): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_OFF_WEEK_DAYS) && $offWeekDay->salaries()->doesntExist();
    }

    public function delete(User $user, OffWeekDay $offWeekDay): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_OFF_WEEK_DAYS) && $offWeekDay->salaries()->doesntExist();
    }
}
