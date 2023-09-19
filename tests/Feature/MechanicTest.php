<?php

use App\Models\SaleList;
use Database\Factories\UserFactory;
use Database\Seeders\MechanicSeeder;
use App\Enums\UserType;
use Laravel\Sanctum\Sanctum;
use App\Models\Mechanic;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\SaleListSeeder;
use function Pest\Laravel\delete;


uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
});

test('mechanic index', function () {
    auth()->user()->givePermissionTo(\App\Enums\PermissionEnum::VIEW_MECHANIC);
    $this->seed([MechanicSeeder::class]);
    $res = $this->get('api/mechanics')->assertStatus(200);
    expect($res->json('data.total'))->toEqual(Mechanic::count());
});

test('mechanic can be created', function () {
    auth()->user()->givePermissionTo(\App\Enums\PermissionEnum::CREATE_MECHANIC);
    $res = $this->post('api/mechanics', ['name' => 'mechanic 1']);
    $res->assertStatus(200);
    expect(Mechanic::count())->toEqual(1);
});

test('mechanic can be updated', function () {
    $mechanic = Mechanic::factory()->create();
    auth()->user()->givePermissionTo(\App\Enums\PermissionEnum::UPDATE_MECHANIC);
    $res = $this->put('api/mechanics/' . $mechanic->id, ["name" => "new mechanic"]);
    $res->assertStatus(200);
    $mechanic->refresh();
    expect(Mechanic::count())->toEqual(1);
});

test('mechanic can be deleted', function () {
    Mechanic::factory(5)->create();
    SaleList::factory()->inventorySale()->create();
    $mechanicCounts = Mechanic::count();
    $mechanic = Mechanic::query()->whereHas('saleLists')->inRandomOrder()->first();
    delete('api/mechanics/' . $mechanic->id)->assertStatus(403);
    auth()->user()->givePermissionTo(\App\Enums\PermissionEnum::DELETE_MECHANIC);

    delete('api/mechanics/' . $mechanic->id)->assertStatus(422);

    $mechanic = Mechanic::query()->whereDoesntHave('saleLists')->inRandomOrder()->first() ?: Mechanic::factory()->createOne();
    $res = $this->delete('api/mechanics/' . $mechanic->id);
    $res->assertStatus(200);

    expect(Mechanic::count())->toEqual($mechanicCounts - 1);
});
