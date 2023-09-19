<?php

use App\Enums\GlobalOptionEnum;
use App\Enums\UserType;
use App\Models\GlobalOption;
use App\Models\Product;
use App\Models\PurchaseList;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\Stockholder;
use Database\Factories\PurchaseListFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SaleListSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\StockholderSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::SuperAdmin])->create();
    Sanctum::actingAs($user);
    $this->withoutExceptionHandling();
    $this->withoutDeprecationHandling();
});

test('purchases tests works', function () {
    $this->seed([PurchaseListSeeder::class]);

    $res = get('api/statistics/purchases?as-groups=false');

    $res->assertStatus(200);

    $purchaseLists = PurchaseList::query()->get();
    $purchaseLists->load(['purchaseItems', 'bill', 'billPayments']);
    $confirmedPurchaseLists = (clone $purchaseLists)->where('is_confirmed', true);
    $unConfirmedPurchaseLists = (clone $purchaseLists)->where('is_confirmed', false);
    expect($purchaseLists->count())->toEqual($confirmedPurchaseLists->count() + $unConfirmedPurchaseLists->count());
    $totalItems = $confirmedPurchaseLists->sum(fn(PurchaseList $purchaseList) => $purchaseList->purchaseItems->count());
    $totalPieces = $confirmedPurchaseLists->sum->total_pieces;
    $totalPrices = $confirmedPurchaseLists->sum->total_price;
    $payedPrices = $confirmedPurchaseLists->sum(fn($p) => $p->bill?->payed_price);
    $remainingPrices = $confirmedPurchaseLists->sum(fn($p) => $p->bill?->remaining_price);

    expect(round($totalPrices, 2))->toEqual(round($confirmedPurchaseLists->sum(fn($p) => $p->bill?->total_price), 2))
        ->and(round($totalPrices, 2))->toEqual(round($remainingPrices + $payedPrices, 2));

    $res->assertJsonPath('data.records.lists_count', $purchaseLists->count());
    $res->assertJsonPath('data.records.confirmed_lists_items_count', $totalItems);
    expect((float)$res->json('data.records.confirmed_lists_items_pieces_count'))->toBe((float)$totalPieces)
        ->and(round($totalPrices, 2))->toEqual(round($res->json('data.records.confirmed_lists_total_price_usd'), 2))
        ->and(round($res->json('data.records.confirmed_lists_payed_price_usd'), 2))->toBe(round($payedPrices, 2))
        ->and(round($remainingPrices, 2))->toEqual(round($res->json('data.records.confirmed_lists_remaining_price_usd'), 2));
});

test('suppliers stats', function () {
    $this->seed([PurchaseListSeeder::class]);
    get('api/statistics/suppliers')->assertStatus(200);
});

test('products stats', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);

    $res = get('api/statistics/products');
    $res->assertStatus(200);
});

test('products transactions stats', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class, PurchaseListSeeder::class, SaleListSeeder::class]);

    $res = get('api/statistics/product_transactions');
    $res->assertStatus(200);
});

test('services stats', function () {
    $this->seed([ProductSeeder::class, ServiceSeeder::class, SaleListSeeder::class]);

    $res = get('api/statistics/services');
    $res->assertStatus(200);
});

test('earnings test', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class, PurchaseListSeeder::class, SaleListSeeder::class, StockholderSeeder::class]);
    $res = get('api/statistics/earnings');
    $res->assertStatus(200);
});


test('sale item earn price works', function () {
    GlobalOption::query()->find(GlobalOptionEnum::CurrencyValue)->update(['value' => 1500]);
    $product = Product::factory()->createOne(['store' => 5, 'sale_price' => 5]);
    $product->initialStore->update(['price' => 3, 'currency_value' => 1500]);
    $saleList = SaleList::factory()->setWithItems(false)->createOne();
    $saleItem = SaleItem::factory()->for($saleList)->createOne(['count' => 3]);
    Stockholder::factory(2)->create();
    $saleList->confirm();
    expect($saleItem->earn_price_in_usd)->toBe(6.0);
    $res = get('api/statistics/earnings')->assertStatus(200);
    $stats = collect($res->json('data.records'));

    expect($stats->firstWhere('name', 'products_earn_price_iqd')['value'])->toBe(9000)
        ->and($stats->firstWhere('name', 'products_earn_price_usd')['value'])->toBe(6);

});
