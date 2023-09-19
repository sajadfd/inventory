<?php

namespace Modules\HR\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\HR\Entities\Employer;
use Modules\HR\Enums\HRPermissionEnum;

class EmployerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_EMPLOYERS);
    }

    public function view(User $user, Employer $employer): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::VIEW_EMPLOYERS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_EMPLOYERS);
    }

    public function update(User $user, Employer $employer): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_EMPLOYERS);
    }

    public function delete(User $user, Employer $employer): bool
    {
        return $user->hasPermissionTo(HRPermissionEnum::MODIFY_EMPLOYERS);
    }

}
