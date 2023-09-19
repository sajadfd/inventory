<?php

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Bill;
use App\Models\InitialStore;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\Supplier;
use App\Models\User;
use Database\Factories\PurchaseListFactory;
use Database\Factories\UserFactory;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
});

test('purchase list index', function () {
    seed([PurchaseListSeeder::class]);

    $res = $this->get('/api/purchase_lists');
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual(PurchaseList::count());
});

test('purchase list can be created', function () {
    $supplier = Supplier::factory()->createOne();
    PurchaseList::factory(3)->for($supplier)->setWithItems(true)->create()->each(fn($i) => $i->confirm());
    $oldSupplierPurchaseListsCount = $supplier->purchaseLists()->count();
    $oldBillsCount = Bill::count();

    $res = $this->post('/api/purchase_lists', [
        'supplier_id' => $supplier->id,
        'date' => now(),
    ]);

    $res->assertStatus(200);
    expect($supplier->purchaseLists()->count())->toEqual($oldSupplierPurchaseListsCount + 1)
        ->and(Bill::count())->toEqual($oldBillsCount);
});

test('purchase list can be edited', function () {
    $supplier = Supplier::factory()->createOne();
    $purchaseList = PurchaseList::factory()->for($supplier)->createOne();
    $res = put('/api/purchase_lists/' . $purchaseList->id, [
        'date' => now(),
    ]);

    $res->assertStatus(200);
});

test('purchase list supplier id can be edited', function () {
    $supplier = Supplier::factory()->createOne();
    PurchaseList::factory(3)->for($supplier)->create();
    $purchaseList = PurchaseList::factory()->for($supplier)->createOne();
    $oldSupplierPurchaseListsCount = $supplier->purchaseLists()->count();
    $supplier2 = Supplier::factory()->createOne();
    PurchaseList::factory(3)->for($supplier2)->create();
    $oldSupplier2PurchaseListsCount = $supplier2->purchaseLists()->count();

    $res = $this->put('/api/purchase_lists/' . $purchaseList->id, [
        'supplier_id' => $supplier2->id,
    ]);

    $res->assertStatus(200);
    expect($supplier->purchaseLists()->count())->toEqual($oldSupplierPurchaseListsCount - 1)
        ->and($supplier2->purchaseLists()->count())->toEqual($oldSupplier2PurchaseListsCount + 1);
});

test('purchase list can be confirmed',
    /**
     * @throws Throwable
     */
    function () {
        $supplier = Supplier::factory()->createOne();
        PurchaseList::factory()->for($supplier)->createOne()->confirm();
        $purchaseList = PurchaseList::factory()->for($supplier)->createOne();

        $billsCount = $supplier->bills()->count();
        $oldSupplierDebts = $supplier->debts;

        $productsList = [];
        $oldProductsListStores = [];
        $expectedStoreIncrement = [];
        $purchaseList->purchaseItems->each(function (PurchaseItem $purchaseItem) use (&$expectedStoreIncrement, &$productsList, &$oldProductsListStores) {
            $productsList[$purchaseItem->product->id] = $purchaseItem->product;
            $oldProductsListStores[$purchaseItem->product->id] = $purchaseItem->product->store;

            $expectedStoreIncrement[$purchaseItem->product->id] = $expectedStoreIncrement[$purchaseItem->product->id] ?? 0;
            $expectedStoreIncrement[$purchaseItem->product->id] += $purchaseItem->count;
        });
        $res = $this->post("api/purchase_lists/$purchaseList->id/confirm");

        $res->assertStatus(200);
        $purchaseList->refresh();
        $supplier->refresh();
        expect($supplier->bills()->count())->toEqual($billsCount + 1)
            ->and(round($supplier->debts, 2))->toEqual(round($oldSupplierDebts + $purchaseList->total_price, 2));

        foreach ($productsList as $productId => $product) {
            expect($product->refresh()->store)->toEqual($oldProductsListStores[$productId] + $expectedStoreIncrement[$productId]);
        }
    });

test('purchase list can be confirmed with auto pay',
    /**
     * @throws Throwable
     */
    function () {
        $supplier = Supplier::factory()->createOne();
        PurchaseList::factory()->for($supplier)->createOne()->confirm();

        $purchaseList = PurchaseList::factory()->for($supplier)->create();

        $oldSupplierDebts = $supplier->debts;

        $res = $this->post("api/purchase_lists/$purchaseList->id/confirm", ['auto_pay' => true]);
        $res->assertStatus(200);

        $purchaseList->refresh();
        $supplier = $supplier->fresh();
        expect($supplier->debts)->toEqual($oldSupplierDebts);
    });

test('purchase list cannot be confirmed twice',
    /**
     * @throws Throwable
     */
    function () {
        $supplier = Supplier::factory()->createOne();
        $purchaseList = PurchaseList::factory()->for($supplier)->createOne()->confirm();

        $res = $this->post("api/purchase_lists/$purchaseList->id/confirm");

        $res->assertStatus(422);
    });

test('purchase list cannot be modified after confirm',
    /**
     * @throws Throwable
     */
    function () {
        $supplier = Supplier::factory()->createOne();
        $supplier2 = Supplier::factory()->createOne();
        $purchaseList = PurchaseList::factory()->for($supplier)->createOne()->confirm();

        $res = $this->put('/api/purchase_lists/' . $purchaseList->id, [
            'supplier_id' => $supplier2->id,
        ]);
        $res->assertStatus(422);
    });

test('purchase list can be deleted', function () {
    $supplier = Supplier::factory()->createOne();
    $purchaseList = PurchaseList::factory()->for($supplier)->createOne();
    $res = $this->delete('/api/purchase_lists/' . $purchaseList->id);
    $res->assertStatus(200);
});

test('purchase list cannot be deleted if confirmed',
    /**
     * @throws Throwable
     */
    function () {
        $supplier = Supplier::factory()->createOne();
        $purchaseList = PurchaseList::factory()->for($supplier)->setWithItems(true)->createOne();
        $purchaseList->confirm();
        $res = $this->delete('/api/purchase_lists/' . $purchaseList->id);
        $res->assertStatus(422);
    });


test('list can be unconfirmed', /**
 * @throws Throwable
 */ function () {
    $user = User::factory()->inventoryAdmin()->createOne();
    Sanctum::actingAs($user);
    $oldBillsCount = Bill::count();
    $oldPaymentsCount = Payment::count();

    $purchaseList = PurchaseList::factory()->createOne();
    post("api/purchase_lists/$purchaseList->id/un_confirm")->assertStatus(403);
    $user->givePermissionTo(PermissionEnum::UN_CONFIRM_PURCHASE_LISTS);
    post("api/purchase_lists/$purchaseList->id/un_confirm")->assertStatus(422);

    $purchaseList->confirm(true);
    expect(Bill::count())->toBe($oldBillsCount + 1)
        ->and(Payment::count())->toBe($oldPaymentsCount + 1);
    post("api/purchase_lists/$purchaseList->id/un_confirm")->assertStatus(200);
    $purchaseList->refresh();
    expect(Bill::count())->toBe(Bill::count())
        ->and(Payment::count())->toBe($oldPaymentsCount)
        ->and($purchaseList->is_confirmed)->toBeFalse();
});

test('list cannot be unconfirmed if it has uses', /**
 * @throws Throwable
 */ function () {
    $user = User::factory()->inventoryAdmin()->createOne();
    Sanctum::actingAs($user);
    $user->givePermissionTo(PermissionEnum::UN_CONFIRM_PURCHASE_LISTS);

    Product::factory(3)->create(['store' => 0, 'is_active' => true]);

    $purchaseList = PurchaseList::factory()->setWithItems(true)->createOne();
    $purchaseList->confirm(true);
    SaleList::factory()->setWithItems(true)->create();

    $oldBillsCount = Bill::count();
    $oldPaymentsCount = Payment::count();

    post("api/purchase_lists/$purchaseList->id/un_confirm")->assertStatus(422);
    $purchaseList->refresh();
    expect(Bill::count())->toBe($oldBillsCount)
        ->and(Payment::count())->toBe($oldPaymentsCount)
        ->and($purchaseList->is_confirmed)->toBeTrue();
});
