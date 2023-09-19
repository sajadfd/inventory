<?php

use App\Enums\UserType;
use App\Models\Category;
use App\Models\Supplier;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
});

test('index works', function () {
    get('api/suppliers')->assertStatus(200);
});
test('supplier can be created', function () {
    $image = UploadedFile::fake()->image('img.png');
    $oldSuppliersCount = Supplier::count();
    $res = $this->post('api/suppliers', ['name' => 'Supplier 1', 'image' => $image]);
    $res->assertStatus(200);
    expect(Supplier::count())->toEqual($oldSuppliersCount + 1)
        ->and(Str::startsWith($res->json('data.image'), config('app.url')))->toBeTrue();
});

test('supplier can be updated', function () {
    $supplier = Supplier::factory()->create();
    $oldSuppliersCount = Supplier::count();
    $res = $this->put('api/suppliers/' . $supplier->id, ['name' => $newName = 'Supplier 1', 'image' => $supplier->image]);
    $res->assertStatus(200);
    $supplier->refresh();
    expect(Supplier::count())->toEqual($oldSuppliersCount)
        ->and($supplier->name)->toEqual($newName);
});

test('supplier can be deleted', function () {
    $this->seed([
        CategorySeeder::class,
        ProductSeeder::class,
        SupplierSeeder::class,
        PurchaseListSeeder::class
    ]);
    Supplier::factory()->create();

    $supplierCounts = Supplier::count();
    $supplier = Supplier::query()->whereHas('purchaseLists')->inRandomOrder()->first();
    $res = $this->delete('api/suppliers/' . $supplier->id);
    $res->assertStatus(422);

    $supplier = Supplier::query()->whereDoesntHave('purchaseLists')->inRandomOrder()->first();
    $res = $this->delete('api/suppliers/' . $supplier->id);
    $res->assertStatus(200);

    expect(Supplier::count())->toEqual($supplierCounts - 1);
});
