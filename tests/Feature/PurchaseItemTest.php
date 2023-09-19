<?php

use App\Enums\UserType;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use Database\Factories\PurchaseListFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);

    $this->seed([
        ProductSeeder::class,
        SupplierSeeder::class,
        PurchaseListSeeder::class
    ]);
});

test('purchase items of list index', function () {
    $res = $this->get('api/purchase_items');
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual(0);

    $purchaseList = PurchaseItem::query()->inRandomOrder()->first()->purchaseList;
    $res = get('api/purchase_items?purchase_list_id=' . $purchaseList->id);
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual($purchaseList->purchaseItems()->count());
});

test('purchase items can be created', function () {
    $purchaseList = PurchaseListFactory::new()->create();
    $purchaseListItemsCount = $purchaseList->purchaseItems()->count();
    $purchaseListTotalPrice = $purchaseList->total_price;
    $purchaseListItemsPieces = $purchaseList->total_pieces;
    $product = Product::query()->inRandomOrder()->first();
    $productStore = $product->store;

    $this->post('api/purchase_items', [
        'purchase_list_id' => $purchaseList->id,
        'product_id' => $product->id,
    ])->assertStatus(422);

    $res = $this->post('api/purchase_items', [
        'purchase_list_id' => $purchaseList->id,
        'product_id' => $product->id,
        'price' => $price = 15,
        'count' => $count = rand(2, 15),
    ]);
    $res->assertStatus(200);

    $purchaseList = $purchaseList->fresh();
    $product->refresh();
    expect($purchaseList->purchaseItems()->count())->toEqual($purchaseListItemsCount + 1)
        ->and($product->store)->toEqual($productStore)
        ->and($purchaseList->total_pieces)->toEqual($purchaseListItemsPieces + $count)
        ->and($purchaseList->total_price)->toEqual($purchaseListTotalPrice + ($count * $price))
        ->and($res->json('data.total_price'))->toEqual($count * $price);
});

test('purchase items can be created with existing product purchase item', function () {
    $purchaseList = PurchaseList::factory()->createOne();
    $product = Product::factory()->createOne();
    $oldPurchaseItem = PurchaseItem::factory()->for($product)->for($purchaseList)->createOne();

    $purchaseListItemsCount = $purchaseList->purchaseItems()->count();
    $purchaseListTotalPrice = $purchaseList->total_price;
    $purchaseListItemsPieces = $purchaseList->total_pieces;
    $oldPurchaseItemCount = $oldPurchaseItem->count;
    $oldPurchaseItemTotalPrice = $oldPurchaseItem->total_price;
    $product->refresh();

    $res = $this->post('api/purchase_items', [
        'purchase_list_id' => $purchaseList->id,
        'product_id' => $oldPurchaseItem->product_id,
        'price' => $price = $oldPurchaseItem->price,
        'count' => $count = rand(2, 15),
    ]);
    $res->assertStatus(200);

    $oldPurchaseItem = $oldPurchaseItem->refresh();
    $purchaseList = $purchaseList->fresh();
    $product->refresh();
    expect($purchaseList->purchaseItems()->count())->toEqual($purchaseListItemsCount)
        ->and($purchaseList->total_pieces)->toEqual($purchaseListItemsPieces + $count)
        ->and($res->json('data.id'))->toEqual($oldPurchaseItem->id)
        ->and($res->json('data.total_price'))->toEqual(($count + $oldPurchaseItemCount) * $price)
        ->and(round($res->json('data.total_price'), 2))->toEqual(round($oldPurchaseItem->total_price, 2))
        ->and(round($res->json('data.total_price'), 2))->toEqual(round(($incrementTotalPrice = $count * $price) + $oldPurchaseItemTotalPrice, 2))
        ->and($res->json('data.count'))->toEqual($count + $oldPurchaseItemCount)
        ->and(round($purchaseList->purchaseItems()->sum('total_price'), 2))->toEqual(round($purchaseListTotalPrice + ($incrementTotalPrice), 2));
});

test('purchase items can be updated', function () {
    $purchaseList = PurchaseListFactory::new()->create();
    $purchaseItem = $purchaseList->purchaseItems()->inRandomOrder()->first();

    $purchaseListItemsCount = $purchaseList->purchaseItems()->count();
    $purchaseListTotalPrice = $purchaseList->total_price;
    $purchaseListItemsPieces = $purchaseList->total_pieces;

    $product = Product::query()->inRandomOrder()->first();
    $oldPurchaseItemTotalPrice = $purchaseItem->total_price;
    $res = $this->put('api/purchase_items/' . $purchaseItem->id, [
        'product_id' => $product->id,
        'price' => $price = 10,
        'count' => $count = rand(2, 15),
    ]);

    $res->assertStatus(200);

    $purchaseList = $purchaseList->fresh();
    expect($purchaseList->purchaseItems()->count())->toEqual($purchaseListItemsCount)
        ->and($purchaseList->total_pieces)->toEqual($purchaseListItemsPieces + $count - $purchaseItem->count)
        ->and(round($purchaseList->total_price, 2))->toEqual(round($purchaseListTotalPrice + ($count * $price) - $oldPurchaseItemTotalPrice, 2))
        ->and($res->json('data.total_price'))->toEqual($count * $price);

});

test('purchase items can be deleted', function () {
    $purchaseList = PurchaseListFactory::new()->create();
    $purchaseItem = $purchaseList->purchaseItems()->inRandomOrder()->first();

    $purchaseListItemsCount = $purchaseList->purchaseItems()->count();
    $purchaseListTotalPrice = $purchaseList->total_price;
    $purchaseListItemsPieces = $purchaseList->total_pieces;

    $res = $this->delete('api/purchase_items/' . $purchaseItem->id);

    $res->assertStatus(200);

    $purchaseList = $purchaseList->fresh();
    expect($purchaseList->purchaseItems()->count())->toEqual($purchaseListItemsCount - 1)
        ->and($purchaseList->total_pieces)->toEqual($purchaseListItemsPieces - $purchaseItem->count)
        ->and(round($purchaseList->total_price, 2))->toEqual(round($purchaseListTotalPrice - $purchaseItem->total_price, 2));
});

test('purchase items cannot be updated or deleted if confirmed', function () {
    $purchaseList = PurchaseListFactory::new()->create()->confirm();
    $purchaseItem = $purchaseList->purchaseItems()->inRandomOrder()->first();

    $this->put('api/purchase_items/' . $purchaseItem->id, ['count' => $count = rand(2, 15)])->assertStatus(422);
    $this->delete('api/purchase_items/' . $purchaseItem->id)->assertStatus(422);
});
