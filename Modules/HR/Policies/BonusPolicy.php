<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Bonus;
use Modules\HR\Enums\HRPermissionEnum;

class BonusPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_BONUSES);
    }

    public function view(User $user, Bonus $bonus): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_BONUSES);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_BONUSES);
    }

    public function update(User $user, Bonus $bonus): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_BONUSES) && $bonus->salary()->doesntExist();
    }

    public function delete(User $user, Bonus $bonus): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_BONUSES) && $bonus->salary()->doesntExist();
    }
}
