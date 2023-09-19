<?php

use App\Enums\PermissionEnum;
use App\Enums\ProductUnitType;
use App\Enums\UserType;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
});

test('endpoints require authenticated used', function () {
    $productUnit = ProductUnit::factory()->createOne();
    $this->app->get('auth')->forgetGuards();
    get(route('product_units.index'))->assertStatus(401);
    get(route('product_units.show', $productUnit))->assertStatus(401);
    post(route('product_units.store'))->assertStatus(401);
    put(route('product_units.update', $productUnit),)->assertStatus(401);
    delete(route('product_units.destroy', $productUnit))->assertStatus(401);
});
test('endpoints require permissions', function () {
    $user = User::factory()->inventoryAdmin()->createOne();
    Sanctum::actingAs($user);
    $productUnit = ProductUnit::factory()->createOne();
    get(route('product_units.index'))->assertStatus(403);
    get(route('product_units.show', $productUnit))->assertStatus(403);
    post(route('product_units.store'))->assertStatus(403);
    put(route('product_units.update', $productUnit),)->assertStatus(403);
    delete(route('product_units.destroy', $productUnit))->assertStatus(403);
});

test('unit can be created', function () {
    $product = Product::factory()->createOne();
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => 'unit 1',
        'price' => 5000,
        'type' => 'smaller',
        'factor' => 5,
        'is_default' => 1,
    ])->assertStatus(200);

    expect($product->productUnits()->count())->toBe(1);
});

test('unit can be updated', function () {
    $product = Product::factory()->createOne();
    $unit = ProductUnit::factory()->for($product)->createOne();
    put("api/product_units/$unit->id", [
        'product_id' => $product->id,
        'name' => $newName = 'new name',
        'price' => 2000,
        'type' => 'smaller',
        'factor' => 5,
    ])->assertStatus(200);
    expect($product->productUnits()->count())->toBe(1)
        ->and($unit->refresh()->name)->toBe($newName);
});

test('unit can be deleted', function () {
    $product = Product::factory()->createOne();
    $unit = ProductUnit::factory()->for($product)->createOne();
    delete("api/product_units/$unit->id")->assertStatus(200);
    expect($product->productUnits()->count())->toBe(0);
});

test('unit name is unique', function () {
    $product = Product::factory()->createOne();
    $unit = ProductUnit::factory()->for($product)->createOne();
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => $unit->name,
        'price' => 5000,
        'type' => 'smaller',
        'factor' => 5,
        'is_default' => false,
    ])->assertStatus(422);
    expect($product->productUnits()->count())->toBe(1);
});

test('unit name uniqueness ignoring in_active_unit', function () {
    $product = Product::factory()->createOne();
    $unit = ProductUnit::factory()->for($product)->createOne(['is_active' => false]);
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => $unit->name,
        'price' => 5000,
        'type' => 'smaller',
        'factor' => 5,
        'is_default' => 0,
    ])->assertStatus(200);
    expect($product->productUnits()->count())->toBe(2);
});

test('unit type is either smaller or larger', function () {
    $product = Product::factory()->createOne();
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => 'unit 1',
        'price' => 5000,
        'type' => 'bigger',
        'factor' => 5,
        'is_default' => 0,
    ])->assertStatus(422);
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => 'unit 1',
        'price' => 5000,
        'type' => 'larger',
        'factor' => 5,
        'is_default' => 0,
    ])->assertStatus(200);
    expect($product->productUnits()->count())->toBe(1);
});

test('product can have only one default unit', function () {
    $product = Product::factory()->createOne();
    $productUnit = ProductUnit::factory()->for($product)->createOne();
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => 'unit 1',
        'price' => 5000,
        'type' => 'larger',
        'factor' => 5,
        'is_default' => 1,
    ])->assertStatus(200);
    post('api/product_units', [
        'product_id' => $product->id,
        'name' => 'unit 2',
        'price' => 5000,
        'type' => 'smaller',
        'factor' => 5,
        'is_default' => 1,
    ])->assertStatus(200);

    expect($product->productUnits()->where('is_default', true)->count())->toBe(1)
        ->and($product->default_unit)->not->toBeEmpty();
});

test('purchase can have smaller unit', function () {
    $product = Product::factory()->createOne(['store' => 5]);
    $productUnit = ProductUnit::factory()->for($product)->createOne(['factor' => 5, 'type' => ProductUnitType::smaller]);
    $purchaseList = PurchaseList::factory()->setWithItems(false)->createOne();

    $oldProductStore = $product->store;
    post('api/purchase_items', [
        'purchase_list_id' => $purchaseList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'price' => 5,
        'count' => $count = 10
    ])->assertStatus(200);
    post("api/purchase_lists/$purchaseList->id/confirm");

    $purchaseItem = $purchaseList->purchaseItems()->latest()->first();
    $product = $product->fresh();
    expect($product->store)->toBe($oldProductStore + ($count * ($productUnit->type === ProductUnitType::larger ? $productUnit->factor : (1 / $productUnit->factor))))
        ->and($productUnit->store)->toBe($product->store * ($productUnit->type === ProductUnitType::smaller ? $productUnit->factor : (1 / $productUnit->factor)))
        ->and($purchaseItem->productUnit?->id)->toBe($productUnit->id)
        ->and($product->transactions()->latest('id')->first()->target_product_unit_id)->toBe($productUnit->id);
});

test('purchase can have larger unit', function () {
    $product = Product::factory()->createOne(['store' => 5]);
    $productUnit = ProductUnit::factory()->for($product)->createOne(['factor' => 5, 'type' => ProductUnitType::larger]);
    $purchaseList = PurchaseList::factory()->setWithItems(false)->createOne();

    $oldProductStore = $product->store;
    post('api/purchase_items', [
        'purchase_list_id' => $purchaseList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'price' => 5,
        'count' => $count = 10
    ])->assertStatus(200);
    post("api/purchase_lists/$purchaseList->id/confirm");

    $purchaseItem = $purchaseList->purchaseItems()->latest()->first();
    $product = $product->fresh();
    expect($product->store)->toBe($oldProductStore + ($count * ($productUnit->type === ProductUnitType::larger ? $productUnit->factor : (1 / $productUnit->factor))))
        ->and($productUnit->store)->toBe($product->store * ($productUnit->type === ProductUnitType::smaller ? $productUnit->factor : (1 / $productUnit->factor)))
        ->and($purchaseItem->productUnit?->id)->toBe($productUnit->id)
        ->and($product->transactions()->latest('id')->first()->target_product_unit_id)->toBe($productUnit->id);
});

test('product unit cannot be deleted or updated if it has purchases', function () {
    $product = Product::factory()->createOne();
    $productUnit = ProductUnit::factory()->for($product)->createOne();
    $purchaseList = PurchaseList::factory()->setWithItems(false)->createOne();
    PurchaseItem::factory()->for($purchaseList)->for($product)->for($productUnit)->createOne();
    delete("api/product_units/$productUnit->id")->assertStatus(422);
    put("api/product_units/$productUnit->id", [ProductUnit::factory()->makeOne()->toArray()])->assertStatus(422);
});

test('sale can have smaller unit', function () {
    $product = Product::factory()->createOne(['store' => 50]);
    $productUnit = ProductUnit::factory()->for($product)->createOne(['factor' => 5, 'type' => ProductUnitType::smaller]);
    $saleList = SaleList::factory()->setWithItems(false)->createOne();

    $oldProductStore = $product->store;
    $oldProductUnitStore = $productUnit->store;
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'unit_id' => $productUnit,
        'count' => 251
    ])->assertStatus(422);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'unit_id' => $productUnit,
        'count' => $count = 10
    ])->assertStatus(200);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'unit_id' => $productUnit,
        'count' => 241
    ])->assertStatus(422);

    $saleItem = $saleList->saleItems()->latest()->first();
    $product->refresh();
    $productUnit = $productUnit->fresh();
    expect($product->store)->toBe($oldProductStore - ($count * ($productUnit->type === ProductUnitType::larger ? $productUnit->factor : (1 / $productUnit->factor))))
        ->and($productUnit->store)->toBe($oldProductUnitStore - $count)
        ->and($productUnit->store)->toBe($product->store * ($productUnit->type === ProductUnitType::smaller ? $productUnit->factor : (1 / $productUnit->factor)))
        ->and($saleItem->productUnit?->id)->toBe($productUnit->id);

    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'unit_id' => $productUnit,
        'count' => 240
    ])->assertStatus(200);

    $product->refresh();
    $productUnit->refresh();
    expect($product->store)->toBe($productUnit->store)->toBe(0.0);
});

test('sale can have larger unit', function () {
    $product = Product::factory()->createOne(['store' => 50]);
    $productUnit = ProductUnit::factory()->for($product)->createOne(['factor' => 5, 'type' => ProductUnitType::larger]);
    $saleList = SaleList::factory()->setWithItems(false)->createOne();

    $oldProductStore = $product->store;
    $oldProductUnitStore = $productUnit->store;
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'unit_id' => $productUnit,
        'count' => $count = 1
    ])->assertStatus(200);

    $saleItem = $saleList->saleItems()->latest()->first();
    $product->refresh();
    $productUnit = $productUnit->fresh();
    expect($product->store)->toBe($oldProductStore - ($count * ($productUnit->type === ProductUnitType::larger ? $productUnit->factor : (1 / $productUnit->factor))))
        ->and($productUnit->store)->toBe($oldProductUnitStore - $count)
        ->and($productUnit->store)->toBe($product->store * ($productUnit->type === ProductUnitType::smaller ? $productUnit->factor : (1 / $productUnit->factor)))
        ->and($saleItem->productUnit?->id)->toBe($productUnit->id);
});
test('sale can have random unit', function () {
    $product = Product::factory()->createOne(['store' => 50]);
    $productUnit = ProductUnit::factory()->for($product)->createOne();
    $saleList = SaleList::factory()->setWithItems(false)->createOne();

    $oldProductStore = $product->store;
    $oldProductUnitStore = $productUnit->store;
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'product_unit_id' => $productUnit->id,
        'unit_id' => $productUnit,
        'count' => $count = 1
    ])->assertStatus(200);

    $saleItem = $saleList->saleItems()->latest()->first();
    $product->refresh();
    $productUnit = $productUnit->fresh();
    expect(round($product->store,5))->toBe(round($oldProductStore - ($count * ($productUnit->type === ProductUnitType::larger ? $productUnit->factor : (1 / $productUnit->factor))), 5))
        ->and(round($productUnit->store,5))->toBe(round($oldProductUnitStore - $count, 5))
        ->and($productUnit->store)->toBe($product->store * ($productUnit->type === ProductUnitType::smaller ? $productUnit->factor : (1 / $productUnit->factor)))
        ->and($saleItem->productUnit?->id)->toBe($productUnit->id);
});

test('product unit cannot be deleted or updated if it has sales', function () {
    $product = Product::factory()->createOne(['store' => 500]);
    $productUnit = ProductUnit::factory()->for($product)->createOne();
    $saleList = SaleList::factory()->setWithItems(false)->createOne();
    SaleItem::factory()->for($saleList)->for($product)->for($productUnit)->createOne(['count' => 1]);
    delete("api/product_units/$productUnit->id")->assertStatus(422);
    put("api/product_units/$productUnit->id", [ProductUnit::factory()->makeOne()->toArray()])->assertStatus(422);
});

test('product simple fractional count is properly counted', function () {
    $product = Product::factory()->createOne(['store' => 50, 'main_unit_name' => 'Bottle']);
    $productUnit = ProductUnit::factory()->for($product)->createOne(['type' => ProductUnitType::smaller, 'factor' => 5]);
    $saleList = SaleList::factory()->setWithItems(false)->createOne();
    SaleItem::factory()->for($saleList)->for($product)->for($productUnit)->createOne(['count' => 20]);

    $product->refresh();
    $productUnit->refresh();
    expect($product->store)->toBe(46.0)
        ->and($productUnit->store)->toBe(230.0)
        ->and($productUnit->targetTransactions()->count())->toBe(1)
        ->and($product->initialStore->used)->toBe(20.0 / 5);

});

test('product complex fractional count is properly counted', function () {
    $product = Product::factory()->createOne(['store' => $productStore = 50, 'main_unit_name' => 'Bottle']);
    $productUnit = ProductUnit::factory()->for($product)->createOne(['type' => ProductUnitType::smaller, 'factor' => $factor = 77]);
    $saleList = SaleList::factory()->setWithItems(false)->createOne();
    SaleItem::factory()->for($saleList)->for($product)->for($productUnit)->createOne(['count' => $itemCount = 13]);

    $product->refresh();
    $productUnit->refresh();
    expect(round($product->store, 5))->toBe(round($productStore - ($itemCount / $factor), 5))
        ->and(round($productUnit->store, 5))->toBe(round(($productStore * $factor) - $itemCount, 5))
        ->and($productUnit->targetTransactions()->count())->toBe(1)
        ->and(round($product->initialStore->used,5))->toBe(round($itemCount / $factor,5));
});


