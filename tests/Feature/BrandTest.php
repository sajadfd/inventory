<?php

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Brand;
use Database\Factories\UserFactory;
use Database\Seeders\BrandSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
});

test('brands get 403 error if not authenticated', function () {
    $this->seed([BrandSeeder::class, ProductSeeder::class]);
    get('api/brands')->assertStatus(403);
    get('api/brands/1')->assertStatus(403);
    post('api/brands')->assertStatus(403);
    put('api/brands/1')->assertStatus(403);
    delete('api/brands/1')->assertStatus(403);
});

test('brands can be indexed', function () {
    $this->seed([BrandSeeder::class, ProductSeeder::class]);
    auth()->user()->givePermissionTo(PermissionEnum::VIEW_BRANDS);
    $res= get('api/brands');
    $res->assertStatus(200);
});

test('get brands products', function () {
    $this->seed([BrandSeeder::class, ProductSeeder::class]);
    $brand = Brand::query()->whereHas('products')->inRandomOrder()->first();
    $res = $this->get('api/products?brand_id=' . $brand->id);
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual($brand->products()->count());
});

test('brand can showed', function () {
    $this->seed([BrandSeeder::class, ProductSeeder::class]);
    $brand = Brand::query()->inRandomOrder()->first();
    auth()->user()->givePermissionTo(PermissionEnum::VIEW_BRANDS);
    $res = $this->get('api/brands/' . $brand->id);
    $res->assertStatus(200);
});

test('brand can successfully created', function () {
    auth()->user()->givePermissionTo(PermissionEnum::CREATE_BRANDS);
    $res = $this->post('api/brands', ["name" => "brand 1"]);
    $res->assertStatus(200);
    expect(Brand::count())->toEqual(1);
});

test('brand cannot be created with exiting name', function () {
    auth()->user()->givePermissionTo(PermissionEnum::CREATE_BRANDS);
    $res = $this->post('api/brands', ["name" => "Brand"]);
    $res->assertStatus(200);
    $res = $this->post("api/brands", ["name" => "Brand"]);
    $res->assertStatus(422);
    $this->assertDatabaseHas('brands', ['name' => "Brand"]);
});

test('brand can successfully updated', function () {
    auth()->user()->givePermissionTo(PermissionEnum::UPDATE_BRANDS);
    $this->seed([BrandSeeder::class, ProductSeeder::class]);
    $brand = Brand::query()->inRandomOrder()->first();
    $res = $this->put("api/brands/" . $brand->id, [
        "name" => "NewBrand",
    ]);
    $res->assertStatus(200);
    $brand->refresh();
    expect($brand->name)->toEqual('NewBrand');
});

test('brand cannot be updated to existing name', function () {
    auth()->user()->givePermissionTo(PermissionEnum::UPDATE_BRANDS);
    $brand1 = Brand::factory()->create();
    $brand2 = Brand::factory()->create();

    $res = $this->put('api/brands/' . $brand2->id, [
        "name" => $brand1->name
    ]);

    $res->assertStatus(422);
    $this->assertDatabaseHas('brands', ['name' => $brand2->name]);
});

test('brand can be deleted', function () {
    auth()->user()->givePermissionTo(PermissionEnum::DELETE_BRANDS);
    $brand = Brand::factory()->create();
    delete('api/brands/' . $brand->id)->assertStatus(200);
    expect(Brand::count())->toBe(0);
});
