<?php


use App\Enums\OrderStatusEnum;
use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductLocation;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\BrandSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\ProductLocationSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::first();
    Sanctum::actingAs($user);
});


test('index works', function () {
    seed([ProductLocationSeeder::class]);
    $res = get('api/product_locations')
        ->assertStatus(200);
    $res->assertJsonPath('data.total', ProductLocation::count());
    get('api/product_locations?filter[name]=A&includes=products&sort=name')->assertStatus(200);
});
test('show works', function () {
    $productLocation = ProductLocation::factory()->createOne();
    get('api/product_locations/' . $productLocation->id)
        ->assertStatus(200)
        ->assertJsonPath('data.id', $productLocation->id);
});

test('create works', function () {
    post('api/product_locations', ['name' => "A-1"])
        ->assertStatus(200)
        ->assertJsonPath('data.id', ProductLocation::first()->id);
});


test('update works', function () {
    $productLocation = ProductLocation::factory()->createOne();
    $res = put('api/product_locations/' . $productLocation->id, ['name' => $productLocation->name . "A-1"])
        ->assertStatus(200);
    $productLocation->refresh();
    $res->assertJsonPath('data.id', $productLocation->id)
        ->assertJsonPath('data.name', $productLocation->name);
});
test('delete works', function () {
    $productLocation = ProductLocation::factory()->createOne();
    delete('api/product_locations/' . $productLocation->id)->assertStatus(200);
    expect(ProductLocation::count())->toBe(0);
});

test('delete does not work if products exists', function () {
    $productLocation = ProductLocation::factory()->createOne();
    Product::factory()->for($productLocation)->createOne();
    delete('api/product_locations/' . $productLocation->id)->assertStatus(422);
    expect(ProductLocation::count())->toBe(1);
});

test('authorization works', function () {
    ProductLocation::factory()->createOne();
    $this->app->get('auth')->forgetGuards();
    get('api/product_locations')->assertStatus(401);
    get('api/product_locations/1')->assertStatus(401);
    post('api/product_locations')->assertStatus(401);
    put('api/product_locations/1')->assertStatus(401);
    delete('api/product_locations/1')->assertStatus(401);
});
test('policy works', function () {
    $productLocation = ProductLocation::factory()->createOne();
    $user = User::factory()->inventoryAdmin()->createOne();
    Sanctum::actingAs($user);
    get('api/product_locations')->assertStatus(403);
    get('api/product_locations/' . $productLocation->id)->assertStatus(403);
    post('api/product_locations')->assertStatus(403);
    put('api/product_locations/' . $productLocation->id)->assertStatus(403);
    delete('api/product_locations/' . $productLocation->id)->assertStatus(403);
});

test('products index filtered by location works', function () {
    seed([ProductLocationSeeder::class, ProductSeeder::class]);
    $productLocation = ProductLocation::query()->whereHas('products')->first();
    get('api/products?filter[product_location_id]=' . $productLocation->id)
        ->assertStatus(200)
        ->assertJsonPath('data.total', $productLocation->products()->count());
});
