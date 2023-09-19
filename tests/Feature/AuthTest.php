<?php

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
});


test('user can log in', function () {
    $user = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    if (auth()->check()) {
        auth()->logout();
    }
    expect(auth()->check())->toBeFalse();
    $res = $this->post('api/login', ['username' => $user->username, 'password' => 1234]);
    expect($user->tokens()->count())->toEqual(1);
    expect(auth()->check())->toBeTrue();
    $res->assertStatus(200);
});

test('user can log out', function () {
    $user = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
    $res = $this->post('api/logout');
    $res->assertStatus(200);
});

test('users index', function () {
    $this->get('api/users')->assertStatus(401);

    $user = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
    $this->get('api/users')->assertStatus(403);
    $user->givePermissionTo(PermissionEnum::VIEW_USERS);
    $res = $this->get('api/users');

    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual(User::count());
});

test('can get all permissions', function () {
    $this->get('api/get-all-permissions')->assertStatus(401);
    $user = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
    $this->get('api/get-all-permissions')->assertStatus(403);
    $user->givePermissionTo(PermissionEnum::UPDATE_PERMISSIONS);
    $this->get('api/get-all-permissions')->assertStatus(200);
});

test('can add user', function () {
    $user = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
    $this->post('api/add-user', [])->assertStatus(403);
    $user->givePermissionTo(PermissionEnum::UPDATE_USERS);
    $this->post('api/add-user', [])->assertStatus(422);
    $res = $this->post('api/add-user', [
        'phone' => '12345678',
        'username' => 'user1',
        'password' => 1234,
        'code' => 'IQ',
        'type' => UserType::INVENTORY_ADMIN->value
    ]);
    $res->assertStatus(200);
    expect(User::count())->toEqual(3);
});

test('can give permission to user', function () {
    $user1 = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    expect($user1->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS))->toBeFalse();
    $this->post('api/give-permission-to/' . $user1->id)->assertStatus(401);
    Sanctum::actingAs($user1);
    $this->post('api/give-permission-to/' . $user1->id)->assertStatus(403);

    $user2 = UserFactory::new(['password' => 1234, 'type' => UserType::INVENTORY_ADMIN])->create();
    $user2->givePermissionTo(PermissionEnum::UPDATE_PERMISSIONS);
    Sanctum::actingAs($user2);
    $this->post('api/give-permission-to/' . $user1->id)->assertStatus(422);
    $res = $this->post('api/give-permission-to/' . $user1->id, [
        'permission' => PermissionEnum::UPDATE_PERMISSIONS
    ]);
    $res->assertStatus(200);
    $user1->refresh();
    expect($user1->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS))->toBeTrue();

    $res = $this->post('api/give-permission-to/' . $user1->id, [
        'permission' => PermissionEnum::UPDATE_PERMISSIONS,
        'allow' => false,
    ]);
    $res->assertStatus(200);
    $user1->refresh();
    expect($user1->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS))->toBeFalse();
});

test('admin can update user password and phone', function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN, 'password' => 12345678])->create();
    $user->givePermissionTo(PermissionEnum::UPDATE_USERS);
    $user2 = UserFactory::new(['type' => UserType::INVENTORY_ADMIN, 'password' => 12345678])->create();

    Sanctum::actingAs($user);
    $res = $this->put('api/users/' . $user2->id, [
        'username' => $newUsername = 'user2',
        'phone' => $newUserPhone = '7819884906',
        'password' => $newPassword = 4321,
        'code' => 'US',
        'my_password' => 12345678,
    ]);
    $res->assertStatus(200);
    $this->app->get('auth')->forgetGuards();

    $res = $this->post('api/login', ['username' => $newUsername, 'password' => $newPassword]);
    $res->assertStatus(200);
    $this->app->get('auth')->forgetGuards();

    $res = $this->post('api/login', ['phone' => $newUserPhone, 'password' => $newPassword]);
    $res->assertStatus(200);
});


test('test that notifications works', function () {
    $user = User::first();
    $tokenId = 'cLEjSSdATMG4sZW0F4Qp1b:APA91bF_Rjl4G1tMcM3-4C8vjuSEUCnqkYcbwQqnW6cqhj_ZfF_llDNsX1YTUJPLdiW-KOTJUm34Bg_vMOYKWYjCQHD7Xt5YhYskPS91HwjJoxbphP3BtYeH8_teD7NoX1wY6hqtqd4v';
    $user->createToken($tokenId);

    $res = \Pest\Laravel\post('api/register-customer', [
        'phone' => '7819884906',
        'code' => 'IQ',
        'username' => 'customer',
        'password' => '12345678',
        'first_name' => 'حسن',
        'last_name' => 'علي',
        'address' => 'Address',
        'national_identification_number' => 12345678,
        'device_token' => 'device_token'
    ]);

    $res->assertStatus(200);

    expect($user->notifications()->count())->toBe(1);

});
