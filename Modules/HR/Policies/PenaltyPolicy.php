<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Bonus;
use Modules\HR\Entities\Penalty;
use Modules\HR\Enums\HRPermissionEnum;

class PenaltyPolicy
{
    use HandlesAuthorization;

    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_PENALTIES);
    }

    public function view(User $user, Penalty $penalty): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_PENALTIES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_PENALTIES);
    }

    public function update(User $user, Penalty $penalty): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_PENALTIES);
    }

    public function delete(User $user, Penalty $penalty): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_PENALTIES);
    }
}
