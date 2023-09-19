<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\OffDate;
use Modules\HR\Enums\HRPermissionEnum;

class OffDatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_OFF_DATES);
    }

    public function view(User $user, OffDate $offDate): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_OFF_DATES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_OFF_DATES);
    }

    public function update(User $user, OffDate $offDate): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_OFF_DATES) && $offDate->salaries()->doesntExist();
    }

    public function delete(User $user, OffDate $offDate): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_OFF_DATES) && $offDate->salaries()->doesntExist();
    }
}
