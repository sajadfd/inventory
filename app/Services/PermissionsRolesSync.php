<?php

namespace App\Services;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsRolesSync
{

    private ?Command $command;
    private array $existing_roles = [];
    private array $existing_permissions = [];

    public function roles(): array
    {
        return [
            RoleEnum::SuperAdmin => [],
            RoleEnum::INVENTORY_ADMIN => [
                PermissionEnum::CREATE_CATEGORIES,
                PermissionEnum::UPDATE_CATEGORIES,
                PermissionEnum::DELETE_CATEGORIES,

                PermissionEnum::CREATE_PRODUCTS,
                PermissionEnum::UPDATE_PRODUCTS,
                PermissionEnum::DELETE_PRODUCTS,

                PermissionEnum::VIEW_SUPPLIERS,
                PermissionEnum::CREATE_SUPPLIERS,
                PermissionEnum::UPDATE_SUPPLIERS,
                PermissionEnum::DELETE_SUPPLIERS,

                PermissionEnum::VIEW_PURCHASE_LISTS,
                PermissionEnum::CREATE_PURCHASE_LISTS,
                PermissionEnum::UPDATE_PURCHASE_LISTS,
                PermissionEnum::DELETE_PURCHASE_LISTS,
                PermissionEnum::CONFIRM_PURCHASE_LISTS,
                PermissionEnum::AUTO_PAY_PURCHASE_LISTS,

                PermissionEnum::CREATE_PURCHASE_ITEMS,
                PermissionEnum::UPDATE_PURCHASE_ITEMS,
                PermissionEnum::DELETE_PURCHASE_ITEMS,

                PermissionEnum::CREATE_CUSTOMERS,
                PermissionEnum::UPDATE_CUSTOMERS,
                PermissionEnum::DELETE_CUSTOMERS,
            ],
            RoleEnum::Customer => [
                PermissionEnum::VIEW_CAR_TYPES,
                PermissionEnum::VIEW_CAR_MODELS,
                PermissionEnum::VIEW_COLORS,
            ],
            RoleEnum::Driver => [],

        ];
    }

    public function directPermissions(): array
    {
        return PermissionEnum::getAllValues();
    }


    public function __construct()
    {
        $this->existing_roles = Role::query()->pluck("name")->toArray();
        $this->existing_permissions = Permission::query()->pluck("name")->toArray();
    }

    public function syncDirectPermissions()
    {
        foreach ($this->directPermissions() as $permission) {
            if (!in_array($permission, $this->existing_permissions)) {
                Permission::create(['name' => $permission]);
                $this->existing_permissions[] = $permission;
                $this->command?->info("Permission Created:" . $permission);
            }
        }
    }

    public function syncRoles()
    {
        foreach ($this->roles() as $roleName => $role_permissions) {
            if (!in_array($roleName, $this->existing_roles)) {
                /** @var Role $role */
                $role = Role::query()->create(['name' => $roleName]);
                $this->command?->info("Role Created:" . $roleName);
            } else {
                $role = Role::query()->where('name', $roleName)->first();
            }

            foreach ($role_permissions as $role_permission) {
                if (!in_array($role_permission, $this->existing_permissions)) {
                    Permission::create(['name' => $role_permission]);
                    $this->existing_permissions[] = $role_permission;
                    $this->command?->info("Role Permission Created:" . $role_permission);
                }
            }

            $role->syncPermissions($role_permissions);
            $this->command?->info("Role Synchronized" . $role);

        }
    }

    public function run(?Command $command = null)
    {

        $this->command = $command;

        $command?->info("Starting...");

        $this->syncDirectPermissions();
        $this->syncRoles();


        /** @var Role $superAdmin */
        $superAdmin = Role::query()->where('name', RoleEnum::SuperAdmin)->first();

        $superAdmin->syncPermissions(Permission::query()->pluck('id')->toArray());


    }


}
