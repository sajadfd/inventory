<?php

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Brand;
use App\Models\Stockholder;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\BrandSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\StockholderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
});

test('endpoints need auth', function () {
    $this->app->get('auth')->forgetGuards();
    get('api/stockholders')->assertStatus(401);
    get('api/stockholders/1')->assertStatus(401);
    post('api/stockholders')->assertStatus(401);
    put('api/stockholders/1')->assertStatus(401);
    delete('api/stockholders/1')->assertStatus(401);
});
test('endpoints need policy', function () {
    $stockholder = Stockholder::factory()->createOne();
    Sanctum::actingAs(User::factory()->inventoryAdmin()->createOne());
    get('api/stockholders')->assertStatus(403);
    get('api/stockholders/' . $stockholder->id)->assertStatus(403);
    post('api/stockholders')->assertStatus(403);
    put('api/stockholders/' . $stockholder->id)->assertStatus(403);
    delete('api/stockholders/' . $stockholder->id)->assertStatus(403);
});

test('index work', function () {
    seed(StockholderSeeder::class);
    get('api/stockholders?filter[name]=A')->assertStatus(200);
});

test('show work', function () {
    $stockholder = Stockholder::factory()->createOne();
    get('api/stockholders/' . $stockholder->id)->assertStatus(200);
});

test('store works', function () {
    post('api/stockholders', ['name' => 'Ahmed', 'store_stocks' => 0, 'inventory_stocks' => 1])->assertStatus(200);
    post('api/stockholders', ['name' => 'Ahmed', 'store_stocks' => 0, 'inventory_stocks' => 1])->assertStatus(422);
    post('api/stockholders', ['store_stocks' => 0, 'inventory_stocks' => 1])->assertStatus(422);
    post('api/stockholders', ['name' => 'Ahmed2', 'inventory_stocks' => 1])->assertStatus(422);
    post('api/stockholders', ['name' => 'Ahmed2', 'store_stocks' => 1])->assertStatus(422);
    expect(Stockholder::count())->toBe(1);
});
test('update works', function () {
    $stockholder1 = Stockholder::factory()->createOne();
    $stockholder2 = Stockholder::factory()->createOne();
    put('api/stockholders/' . $stockholder1->id, ['name' => $stockholder1->name, 'store_stocks' => 5, 'inventory_stocks' => 5])->assertStatus(200);
    put('api/stockholders/' . $stockholder1->id, ['name' => $stockholder2->name, 'store_stocks' => 5, 'inventory_stocks' => 5])->assertStatus(422);
    put('api/stockholders/' . $stockholder1->id, ['name' => $stockholder1->name . 'A', 'store_stocks' => 2, 'inventory_stocks' => 2])->assertStatus(200);

    $stockholder1->refresh();
    expect(Stockholder::count())->toBe(2)
        ->and($stockholder1->store_stocks)->toBe(2.0)
        ->and($stockholder1->inventory_stocks)->toBe(2.0);
});
test('delete work', function () {
    $stockholder = Stockholder::factory()->createOne();
    delete('api/stockholders/' . $stockholder->id)->assertStatus(200);
    expect(Stockholder::count())->toBe(0);
});
