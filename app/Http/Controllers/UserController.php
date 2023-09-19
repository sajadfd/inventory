<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterCustomerRequest;
use App\Http\Requests\User\SetOptionRequest;
use App\Http\Requests\User\UpdatePermissionRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use App\Services\PaginatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        $userQuery = User::query()->where('type', '!=', UserType::Customer);
        if (auth()->user()->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS)) {
            $userQuery->with(['permissions', 'roles']);
        }
        return ApiResponse::success(PaginatorService::from($userQuery, UserResource::class));
    }

    public function show(User $user)
    {
        if (auth()->user()->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS)) {
            $user->load(['permissions', 'roles']);
        }
        return ApiResponse::success(UserResource::make($user));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->fill($request->validated());
        if ($user->isDirty('type')) {
            if ($user->getOriginal('type') === UserType::SuperAdmin && User::query()->where('type', UserType::SuperAdmin)->count() === 1) {
                throw  ValidationException::withMessages([__('There must be one super admin')]);
            }

            $user->roles()->each(function (Role $role) use ($user) {
                $user->removeRole($role);
            });
            $user->assignRole($user->type->value);
        }
        $user->save();
        $user->load('permissions');
        return ApiResponse::success(UserResource::make($user));
    }

    public function showCurrent()
    {
        $user = auth()->user();
        $user->load(['permissions', 'roles']);
        return ApiResponse::success(UserResource::make($user));
    }

    public function getAllPermissions()
    {
        $this->authorize('getAllPermissions', new User);
        $permissionNames = Permission::query()->pluck('name')->toArray();
        $roleNames = Role::query()->pluck('name')->toArray();
        return ApiResponse::success(['roles' => array_map(fn($item) => [
            'value' => $item,
            'title' => __($item),
        ], $roleNames), 'permissions' => array_map(fn($item) => [
            'value' => $item,
            'title' => __($item),
        ], $permissionNames)]);
    }

    public function getUserPermissions(User $user)
    {
        $this->authorize('getUserPermissions', $user);
        return ApiResponse::success(['roles' => array_map(fn($item) => [
            'value' => $item,
            'title' => __($item),
        ], $user->getRoleNames()->toArray()), 'permissions' => array_map(fn($item) => [
            'value' => $item,
            'title' => __($item),
        ], $user->getAllPermissions()->pluck('name')->toArray())]);
    }

    public function givePermissionTo(UpdatePermissionRequest $request, User $user)
    {
        if ($request->boolean('allow', true)) {
            $user->givePermissionTo($request->validated('permission'));
        } else {
            $user->revokePermissionTo($request->validated('permission'));
        }
        $user->permissions->transform(function ($permission) {
            return $permission['name'];
        });
        $user->roles->transform(function ($role) {
            return $role['name'];
        });
        return ApiResponse::success(UserResource::make($user));
    }

    public function setOption(SetOptionRequest $request)
    {
        //TODO: Fix
        $user = auth()->user();
        $options = $user->options;
        $options[$request->get('option_name')] = $request->get('option_value');
        $request->options = $options;
        $request->save();
        return ApiResponse::success($options);
    }

    public function getOption($option)
    {
        //TODO: Fix
        $optionValue = auth()->user()->options[$option] ?? null;
        return ApiResponse::success($optionValue);
    }

    public function addUser(AddUserRequest $request)
    {
        DB::beginTransaction();
        $data = $request->validated();
        $user = User::query()->create($data);
        $profile = $user->profile()->create([]);

        if ($user->type === UserType::Customer) {
            $customer = Customer::query()->findOrFail($request->get('customer_id'));
            $customer->update(['user_id' => $user->id, 'phone' => $user->phone]);
            $profile->update(['address' => $customer->address, 'first_name' => $customer->name, 'image' => $customer->image, 'thumbnail' => $customer->thumbnail]);
        } else if ($user->type === UserType::Driver) {
            $drive = Driver::query()->firstOrCreate([
                'id' => $request->validated('driver_id')
            ], [
                'name' => $user->username,
                'phone' => $user->phone,
            ]);
            $drive->update(['user_id' => $user->id]);
            $profile->update(['address' => $drive->address, 'first_name' => $drive->name, 'image' => $drive->image, 'thumbnail' => $drive->thumbnail]);
        }
        DB::commit();
        return ApiResponse::success(UserResource::make($user), 200);
    }

    public function login(LoginRequest $request)
    {

        $data = Arr::except($request->validated(), ['device_token']);
        if (empty($data['type']) && isset($data['type'])) {
            unset($data['type']);
        }

        $data['is_active'] = true;

        if (auth('web')->attempt($data)) {
            $user = auth('web')->user();
            $deviceToken = $request->get('device_token', '');

            PersonalAccessToken::whereName($deviceToken)->where('name', '!=', '')->whereHas('tokenable', function ($query) use ($user) {
                $query->where('type', $user->type);
            })->delete();

            $token = $user->createToken($deviceToken);

            $user->token = $token->plainTextToken;
            return ApiResponse::success($user);
        } else {
            return ApiResponse::error(__('Invalid Credentials'), 401);
        }
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();
        return ApiResponse::success(true);
    }

    public function registerCustomer(RegisterCustomerRequest $request)
    {
        $data = $request->only(['username', 'phone', 'password', 'code']);
        $user = User::query()->create($data + [
                'type' => UserType::Customer
            ]);

        $user->assignRole(RoleEnum::Customer);

        $profile = $user->profile()->create($request->only(['first_name', 'last_name', 'national_identification_number', 'address']));

        $customerName = $profile->full_name ?: $user->username;

        while (Customer::query()->where('name', $customerName)->exists()) {
            $customerName .= ' _';
        }

        $user->customer()->create(
            [
                'name' => $customerName,
                'phone' => $user->phone,
                'address' => $request->input('address')
            ]
        );

        $deviceToken = $request->validated('device_token', '');
        PersonalAccessToken::whereName($deviceToken)->whereHas('tokenable', function ($query) use ($user) {
            $query->where('type', $user->type);
        })->delete();
        $token = $user->createToken($deviceToken);
        $user->load(['profile', 'customer']);

        $user->token = $token->plainTextToken;

        Role::findByName(RoleEnum::SuperAdmin)->users()->each(fn(User $user) => $user->notify(__("Customer User Registered"), __(":customer has joined as customer", ['customer' => $customerName]), NotificationType::CustomerRegistered));
        return ApiResponse::success($user, 200);
    }
}
