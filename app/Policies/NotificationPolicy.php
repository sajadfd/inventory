<?php
declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_NOTIFICATIONS);
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_NOTIFICATIONS);
    }

}
