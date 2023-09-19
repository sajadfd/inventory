<?php

use App\Enums\UserType;
use App\Models\Product;
use Database\Factories\ProductFactory;
use Database\Factories\SaleListFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\ServiceSeeder;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\ExpectationFailedException;


uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
});

test('sale factory can be created', function () {
    $this->seed([
        CategorySeeder::class,
        ProductSeeder::class,
        PurchaseListSeeder::class,
        ServiceSeeder::class,
    ]);
    $products = Product::get();
    $products->each(function (Product $product) {
        expect($product->calculated_store)->toEqual($product->store);
    });

    $saleList = SaleListFactory::times(3)->create();

    $products = Product::get();
    $products->each(function (Product $product) {
        expect($product->calculated_store)->toEqual($product->store);
        $cnt1 = $product->initialStore?->count;
        $used1 = $product->initialStore?->used;
        $cnt2 = $product->confirmedPurchaseItems->sum('count');
        $used2 = $product->confirmedPurchaseItems->sum('used');

        $cnt3 = $product->saleItems->sum('count');

        expect($used1 + $used2)->toEqual($cnt3);
        expect($product->store)->toEqual($cnt1 + $cnt2 - $cnt3);

    });

    expect(true)->toBeTrue();
});
