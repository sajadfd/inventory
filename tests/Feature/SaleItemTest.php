<?php

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\InitialStore;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use App\Models\SaleItem;
use App\Models\SaleList;
use Database\Factories\SaleListFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SaleListSeeder;
use Database\Seeders\ServiceSeeder;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\ExpectationFailedException;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($this->user);
});

test('sale items of list index', function () {
    seed([SaleListSeeder::class]);
    get('api/sale_items')->assertStatus(403);
    $this->user->givePermissionTo(PermissionEnum::VIEW_SALE_ITEMS);
    $res = get('api/sale_items');
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual(0);

    $saleList = SaleItem::query()->inRandomOrder()->first()->saleList;
    $res = get('api/sale_items?sale_list_id=' . $saleList->id);
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual($saleList->saleItems()->count());
});

test('sale items creating errors', function () {
    $saleList = SaleList::factory()->createOne();
    $product = Product::factory()->createOne(['store' => 5]);

    post('api/sale_items')->assertStatus(403);
    $this->user->givePermissionTo(PermissionEnum::CREATE_SALE_ITEMS);

    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'count' => $product->store + 1,
    ])->assertStatus(422);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'count' => 0,
    ])->assertStatus(422);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'count' => $product->store,
    ])->assertStatus(422);
    post('api/sale_items', [
        'product_id' => $product->id,
        'count' => $product->store,
    ])->assertStatus(422);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
    ])->assertStatus(422);
});

test('sale items can be created', function () {
    $saleList = SaleList::factory()->createOne();
    $saleListItemsCount = $saleList->saleItems()->count();
    $saleListTotalPrice = $saleList->total_price;
    $saleListSaleItemsTotalPieces = $saleList->sale_items_total_pieces;

    $product = Product::factory()->createOne(['store' => 5]);

    $oldStore = $product->store;
    PurchaseList::factory()->has(PurchaseItem::factory(2)->for($product))->create()->confirm();
    $product->refresh();

    $productStore = $product->store;

    $this->user->givePermissionTo(PermissionEnum::CREATE_SALE_ITEMS);
    $res = post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'count' => $count = $oldStore + 2,
    ]);
    $res->assertStatus(200);
    $saleList = $saleList->fresh();
    $saleItem = SaleItem::find($res->json('data.id'));
    $product->refresh();
    expect($saleList->saleItems()->count())->toEqual($saleListItemsCount + 1)
        ->and($product->store)->toEqual($productStore - $count)
        ->and($saleList->sale_items_total_pieces)->toEqual($saleListSaleItemsTotalPieces + $count)
        ->and($saleList->total_price)->toEqual($saleListTotalPrice + ($count * $product->sale_price_in_iqd))
        ->and($res->json('data.total_price'))->toEqual($count * $product->sale_price_in_iqd)
        ->and($saleItem->count)->toEqual($count);

    $loopIndex = 0;
    foreach ($saleItem->transactions as $k => $transaction) {
        expect($saleItem->makeHidden('transactions')->toArray())->toEqual($transaction->targetable->toArray());
        $source = $transaction->sourceable;
        if ($loopIndex + 1 !== $saleItem->transactions->count()) {
            expect($source->used)->toEqual(min($count, $source->count));
        }
        $loopIndex++;
    }
    expect($saleItem->transactions->sum('count'))->toEqual($count);
});


test('sale items can be created with different product price', function () {
    $saleList = SaleList::factory()->createOne();
    $product = Product::factory()->createOne(['store' => 1]);

    $this->user->givePermissionTo(PermissionEnum::CREATE_SALE_ITEMS);
    $res = post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'price' => -5,
        'count' => 1,
    ]);
    $res->assertStatus(200);
    $saleItem = SaleItem::find($res->json('data.id'));
    expect($saleItem->price)->toEqual($product->sale_price_in_iqd);

    $this->user->givePermissionTo(PermissionEnum::MODIFY_SALE_ITEMS_PRICES);
    $product1 = Product::factory()->createOne(['store' => 2]);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product1->id,
        'count' => 1,
    ])->assertStatus(200);

    $product2 = Product::factory()->createOne(['store' => 1]);
    post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product2->id,
        'price' => $product2->latest_purchase_price_in_iqd - 1,
        'count' => 1,
    ])->assertStatus(422);
    $res2 = post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product2->id,
        'price' => $product2->sale_price_in_iqd + 10,
        'count' => 1,
    ])->assertStatus(200);
    $saleItem2 = SaleItem::find($res2->json('data.id'));
    expect($saleItem2->price)->toBe($product2->sale_price_in_iqd + 10);
});

test('sale items can be created with existing product id', function () {
    $saleList = SaleList::factory()->setWithItems(true)->createOne();
    $product = Product::factory()->createOne(['store' => 5]);
    $saleItem = SaleItem::factory()->for($saleList)->for($product)->createOne(['count' => 1, 'price' => $product->sale_price_in_iqd]);

    $saleList->refresh();
    $saleListItemsCount = $saleList->saleItems()->count();
    $saleListTotalPrice = $saleList->total_price;
    $saleListSaleItemsTotalPieces = $saleList->sale_items_total_pieces;
    $product->refresh();
    $productStore = $product->store;
    $oldSaleItemCount = $saleItem->net_count;
    $this->user->givePermissionTo(PermissionEnum::CREATE_SALE_ITEMS);
    $res = $this->post('api/sale_items', [
        'sale_list_id' => $saleList->id,
        'product_id' => $product->id,
        'count' => $count = min(rand(1, 5), $productStore),
    ])->assertStatus(200);

    $saleList = $saleList->fresh();
    $saleItem->refresh();
    $product->refresh();
    expect($saleList->saleItems()->count())->toEqual($saleListItemsCount)
        ->and($res->json('data.id'))->toEqual($saleItem->id)
        ->and($res->json('data.product_id'))->toEqual($saleItem->product_id)
        ->and($product->store)->toEqual($productStore - $count)
        ->and($product->store)->toEqual($product->calculated_store, "Product Calculated Store equal Product Store")
        ->and($saleList->sale_items_total_pieces)->toEqual($saleListSaleItemsTotalPieces + $count)
        ->and($saleList->total_price)->toEqual($saleListTotalPrice + ($count * $product->sale_price_in_iqd))
        ->and($saleList->saleItems()->sum('total_price'))->toEqual($saleList->sale_items_total_price)
        ->and($res->json('data.total_price'))->toEqual(($count + $oldSaleItemCount) * $product->sale_price_in_iqd)
        ->and($saleItem->count)->toEqual($count + $oldSaleItemCount)
        ->and($res->json('data.count'))->toEqual($count + $oldSaleItemCount)
        ->and($saleItem->transactions->sum('count'))->toEqual($count + $oldSaleItemCount);

    $loopIndex = 0;
    foreach ($saleItem->transactions as $k => $transaction) {
        expect($saleItem->loadMissing('productUnit')->makeHidden(['transactions', 'product'])->toArray())->toEqual($transaction->targetable->loadMissing('productUnit')->toArray());
        $source = $transaction->sourceable;
        if ($loopIndex + 1 !== $saleItem->transactions->count()) {
            try {
                expect($source->used)->toEqual(min(($count + $oldSaleItemCount), $source->count));
                //This may fail becuase source maybe used in other sale items;
            } catch (ExpectationFailedException $e) {

                throw $e;
            }
        }
        $loopIndex++;
    }
});

test('sale update errors', function () {
    SaleList::factory()->setWithItems(true)->create();
    $saleItem = SaleItem::query()->inRandomOrder()->first();

    $saleList = SaleListFactory::new()->create();
    $product = Product::query()->where('id', '!=', $saleItem->product_id)->inRandomOrder()->first();
    put('api/sale_items/' . $saleItem->id)->assertStatus(403);
    $this->user->givePermissionTo(PermissionEnum::UPDATE_SALE_ITEMS);

    expect(round($saleItem->product->calculated_store, 5))->toEqual(round($saleItem->product->store, 5));
    put('api/sale_items/' . $saleItem->id, [
        'sale_list_id' => $saleList->id,
    ])->assertStatus(422);
    put('api/sale_items/' . $saleItem->id, [
        'product_id' => $product->id,
    ])->assertStatus(422);
    put('api/sale_items/' . $saleItem->id, [
        'count' => 1,
    ])->assertStatus(200);
    put('api/sale_items/' . $saleItem->id, [
        'count' => 1,
    ])->assertStatus(200);
    $saleItem->saleList->confirm();
    put('api/sale_items/' . $saleItem->id, [
        'count' => 1,
    ])->assertStatus(422);
});

test('sale items can be updated with decrement', function () {
    $saleList = SaleList::factory()->setWithItems(true)->createOne();
    $saleItem = $saleList->saleItems()->where('count', '>', 1)->inRandomOrder()->first()
        ?: SaleItem::factory()->for($saleList)->createOne();
    $saleListItemsCount = $saleList->saleItems()->count();
    $saleListTotalPrice = $saleList->total_price;
    $saleListItemsPieces = $saleList->sale_items_total_pieces;

    $product = $saleItem->product;
    $oldProductStore = $product->store;

    $changeInCount = -1;
    $oldSaleItemTotalPrice = $saleItem->total_price;
    $oldSaleItemCount = $saleItem->count;
    $oldTransactionsCount = $saleItem->transactions()->count();

    $this->user->givePermissionTo(PermissionEnum::UPDATE_SALE_ITEMS);

    $res = $this->put('api/sale_items/' . $saleItem->id, [
        'count' => $count = $saleItem->count + $changeInCount,
    ])->assertStatus(200);

    $saleList = $saleList->fresh();
    expect($saleList->saleItems()->count())->toEqual($saleListItemsCount)
        ->and($saleList->sale_items_total_pieces)->toEqual($saleListItemsPieces + $count - $oldSaleItemCount)
        ->and($saleList->total_price)->toEqual($saleListTotalPrice + ($count * $product->sale_price_in_iqd) - $oldSaleItemTotalPrice)
        ->and($res->json('data.total_price'))->toEqual($count * $product->sale_price_in_iqd);

    $saleItem = $saleItem->fresh();

    expect($saleItem->transactions()->count())->toBeLessThanOrEqual($oldTransactionsCount);

    $loopIndex = 0;
    foreach ($saleItem->transactions as $k => $transaction) {
        expect($saleItem->makeHidden('transactions')->toArray())->toEqual($transaction->targetable->toArray());
        $source = $transaction->sourceable;
        if ($loopIndex + 1 !== $saleItem->transactions->count()) {
            expect($source->used)->toEqual(min($count, $source->count));
        }
        $loopIndex++;
    }
    expect($saleItem->transactions->sum('count'))->toEqual($count);
});

test('sale items can be updated with increment', function () {
    $saleList = SaleList::factory()->setWithItems(true)->createOne();
    $saleItem = $saleList->saleItems()->where('count', '>', 0)->inRandomOrder()->first();
    $saleListItemsCount = $saleList->saleItems()->count();
    $saleListTotalPrice = $saleList->total_price;
    $saleListItemsPieces = $saleList->sale_items_total_pieces;

    $product = $saleItem->product;

    $changeInCount = 1;
    $oldSaleItemTotalPrice = $saleItem->total_price;
    $oldSaleItemCount = $saleItem->count;
    $oldTransactionsCount = $saleItem->transactions()->count();

    $this->user->givePermissionTo(PermissionEnum::UPDATE_SALE_ITEMS);

    $res = $this->put('api/sale_items/' . $saleItem->id, [
        'count' => $count = $saleItem->count + $changeInCount,
    ])->assertStatus(200);

    $saleList = $saleList->fresh();
    expect($saleList->saleItems()->count())->toEqual($saleListItemsCount)
        ->and($saleList->sale_items_total_pieces)->toEqual($saleListItemsPieces + $count - $oldSaleItemCount)
        ->and($saleList->total_price)->toEqual($saleListTotalPrice + ($count * $product->sale_price_in_iqd) - $oldSaleItemTotalPrice)
        ->and($res->json('data.total_price'))->toEqual($count * $product->sale_price_in_iqd);

    $saleItem = $saleItem->fresh();

    expect($saleItem->transactions()->count())->toBeGreaterThan($oldTransactionsCount);

    $loopIndex = 0;
    foreach ($saleItem->transactions as $k => $transaction) {
        expect($saleItem->makeHidden('transactions')->toArray())->toEqual($transaction->targetable->toArray());
        $source = $transaction->sourceable;
        if ($loopIndex + 1 !== $saleItem->transactions->count()) {
            expect($source->used)->toBeGreaterThanOrEqual(min($count, $source->count));
        }
        $loopIndex++;
    }
    expect($saleItem->transactions->sum('count'))->toEqual($count);
});

test('sale items can be deleted', function () {
    $product = Product::factory()->createOne(['store' => 3]);
    $purchaseList = PurchaseList::factory()->setWithItems(false)->createOne();
    PurchaseItem::factory()->for($product)->for($purchaseList)->createOne(['count' => 3]);
    $product->refresh();

    $saleList = SaleList::factory()->setWithItems(false)->createOne();
    $saleItem = SaleItem::factory()->for($saleList)->for($product)->create(['count' => $product->store]);
    $saleListItemsCount = $saleList->saleItems()->count();
    $saleListTotalPrice = $saleList->total_price;
    $saleListSaleItemsTotalPieces = $saleList->sale_items_total_pieces;

    /** @var Product $product */
    $product = $saleItem->product;
    $oldProductStore = $product->store;
    $oldProductTransactionsCount = $product->transactions()->count();
    $saleItemTransactionsCount = $saleItem->transactions()->count();
    $saleItemTransactions = $saleItem->transactions;
    expect($saleItemTransactions->sum('count'))->toEqual($saleItem->net_count);

    $sourcesCount = [
        InitialStore::class => [],
        PurchaseItem::class => []
    ];
    $currentUsed = [
        InitialStore::class => [],
        PurchaseItem::class => []
    ];

    foreach ($saleItemTransactions as $transaction) {
        $source = $transaction->sourceable;
        $sourcesCount[get_class($source)][$source->id] = $sourcesCount[get_class($source)][$source->id] ?? 0;
        $sourcesCount[get_class($source)][$source->id] += $transaction->count;
        $currentUsed[get_class($source)][$source->id] = $source->used;
    }

    $this->user->givePermissionTo(PermissionEnum::DELETE_SALE_ITEMS);
    delete('api/sale_items/' . $saleItem->id)->assertStatus(200);
    $saleList = $saleList->fresh();
    expect($saleList->saleItems()->count())->toEqual($saleListItemsCount - 1)
        ->and($saleList->sale_items_total_pieces)->toEqual($saleListSaleItemsTotalPieces - $saleItem->count)
        ->and($saleList->total_price)->toEqual($saleListTotalPrice - $saleItem->total_price);

    $product = $product->fresh();
    expect($product->store)->toEqual($oldProductStore + $saleItem->net_count)
        ->and($product->transactions()->count())->toEqual($oldProductTransactionsCount - $saleItemTransactionsCount);

    foreach ($currentUsed as $sourceType => $sourcesList) {
        foreach ($sourcesList as $sourceId => $sourceUsed) {
            /** @var PurchaseItem $sourceType */
            $source = $sourceType::find($sourceId);
            expect($source->used)->toEqual($sourceUsed - $sourcesCount[$sourceType][$sourceId]);
        }
    }
});

test('sale items cannot be deleted if list is confirmed', function () {
    $saleList = SaleList::factory()->setWithItems(true)->createOne()->confirm();
    $saleItem = $saleList->saleItems()->inRandomOrder()->first();
    delete('api/sale_items/' . $saleItem->id)->assertStatus(403);
    $this->user->givePermissionTo(PermissionEnum::DELETE_SALE_ITEMS);
    delete('api/sale_items/' . $saleItem->id)->assertStatus(422);
});
