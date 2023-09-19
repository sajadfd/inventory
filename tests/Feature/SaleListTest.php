<?php

use App\Enums\PermissionEnum;
use App\Enums\SaleType;
use App\Models\Bill;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Diagnosis;
use App\Models\Mechanic;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\User;
use Database\Factories\SaleListFactory;
use Database\Seeders\MechanicSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PurchaseListSeeder;
use Database\Seeders\SaleListSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());

    $this->seed([
        MechanicSeeder::class,
        ProductSeeder::class,
        PurchaseListSeeder::class,
        ServiceSeeder::class,
    ]);
});

test('sale list index', function () {
    $this->seed([SaleListSeeder::class]);
    $res = $this->get('/api/sale_lists');
    $res->assertStatus(200);
    expect($res->json('data.total'))->toEqual(SaleList::count());
});

test('sale list of store type can be created', function () {
    $customer = Customer::factory()->has(SaleList::factory(2))->create();
    $oldCustomerSaleListsCount = $customer->saleLists()->count();
    $oldBillsCount = Bill::count();
    $this->post('/api/sale_lists', [
        'customer_id' => $customer->id,
    ])->assertStatus(422);

    $res = $this->post('/api/sale_lists', [
        'customer_id' => $customer->id,
        'date' => now(),
        'type' => SaleType::StoreSale->value
    ]);

    $res->assertStatus(200);
    expect($customer->saleLists()->count())->toEqual($oldCustomerSaleListsCount + 1)
        ->and(Bill::count())->toEqual($oldBillsCount);
});

test('sale list of inventory type can be created', function () {
    $customer = Customer::factory()->has(Car::factory())->has(SaleList::factory(2))->create();
    $oldCustomerSaleListsCount = $customer->saleLists()->count();
    $oldBillsCount = Bill::count();
    $diagnosis = Diagnosis::factory()->create();
    $car = $customer->cars()->inRandomOrder()->first();
    $res = $this->post('/api/sale_lists', [
        'customer_id' => $customer->id,
        'date' => now(),
        'type' => SaleType::InventorySale->value,
        'car_id' => $car->id,
        'mechanic_id' => Mechanic::query()->inRandomOrder()->first()->id,
        'diagnosis_id' => $diagnosis->id,
    ]);

    $res->assertStatus(200);
    expect($customer->saleLists()->count())->toEqual($oldCustomerSaleListsCount + 1)
        ->and(Bill::count())->toEqual($oldBillsCount);
});

test('sale list can be edited', function () {
    $customer = Customer::factory()
        ->has(Car::factory())->has(SaleList::factory(2))
        ->create();
    $saleList = SaleListFactory::new(['customer_id' => $customer->id])->create();
    $res = $this->put('/api/sale_lists/' . $saleList->id, [
        'date' => now(),
        'notes' => '123'
    ]);

    $res->assertStatus(200);
});

test('sale list customer id can be edited', function () {
    $customer = Customer::factory(SaleList::factory(2))->create();
    $saleList = SaleListFactory::new(['customer_id' => $customer->id])->create();
    $customer2 = Customer::factory(SaleList::factory(2))->create();

    $oldCustomerSaleListsCount = $customer->saleLists()->count();
    $oldCustomer2SaleListsCount = $customer2->saleLists()->count();

    $res = $this->put('/api/sale_lists/' . $saleList->id, [
        'customer_id' => $customer2->id,
    ]);

    $res->assertStatus(200);
    expect($customer->saleLists()->count())->toEqual($oldCustomerSaleListsCount - 1)
        ->and($customer2->saleLists()->count())->toEqual($oldCustomer2SaleListsCount + 1);
});

test('sale list can be confirmed',
    /**
     * @throws Throwable
     */
    function () {
        $customer = Customer::factory()->has(SaleList::factory(2))->create();
        SaleListFactory::new(['customer_id' => $customer->id])->create()->confirm();
        $saleList = SaleListFactory::new(['customer_id' => $customer->id])->create();

        $billsCount = $customer->bills()->count();
        $oldCustomerDebts = $customer->debts;

        $res = $this->post("api/sale_lists/$saleList->id/confirm");

        $res->assertStatus(200);
        $saleList->refresh();
        $customer->refresh();
        expect($customer->bills()->count())->toEqual($billsCount + 1)
            ->and($customer->debts)->toEqual($oldCustomerDebts + $saleList->total_price);
    });

test('sale list can be confirmed with auto pay',
    /**
     * @throws Throwable
     */
    function () {
        $customer = Customer::factory()->has(SaleList::factory(2))->create();
        SaleListFactory::new(['customer_id' => $customer->id])->create()->confirm();

        $saleList = SaleListFactory::new(['customer_id' => $customer->id])->create();

        $oldCustomerDebts = $customer->debts;

        $res = $this->post("api/sale_lists/$saleList->id/confirm", ['auto_pay' => true]);
        $res->assertStatus(200);

        $saleList->refresh();
        $customer = $customer->fresh();
        expect($customer->debts)->toEqual($oldCustomerDebts);
    });

test('sale list cannot be confirmed twice',
    /**
     * @throws Throwable
     */
    function () {
        $saleList = SaleList::factory()->createOne()->confirm();
        post("api/sale_lists/$saleList->id/confirm")->assertStatus(422);
    });

test( 'sale list cannot be modified after confirm',/**
 * @throws Throwable
 */ function () {
    $customer = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    $saleList = SaleListFactory::new(['customer_id' => $customer->id])->create()->confirm();

    $res = $this->put('/api/sale_lists/' . $saleList->id, [
        'customer_id' => $customer2->id,
    ]);
    $res->assertStatus(422);
});

test('sale list can be deleted', function () {
    $customer = Customer::factory()->create();
    $saleList = SaleListFactory::new(['customer_id' => $customer->id])->create();

    $productsList = [];
    $productStores = [];
    $expectedProductStores = [];
    $saleList->saleItems()->each(function (SaleItem $saleItem) use (&$productsList, &$productStores, &$expectedProductStores) {
        $productsList[$saleItem->product->id] = $saleItem->product;
        $productStores[$saleItem->product->id] = $saleItem->product->store;
        $expectedProductStores[$saleItem->product->id] = $expectedProductStores[$saleItem->product->id] ?? 0;
        $expectedProductStores[$saleItem->product->id] += $saleItem->net_count;
    });

    $res = $this->delete('/api/sale_lists/' . $saleList->id);
    $res->assertStatus(200);

    foreach ($productsList as $productId => $product) {
        expect($product->refresh()->store)->toEqual($productStores[$productId] + $expectedProductStores[$productId]);
    }
});

test('sale list cannot be deleted if confirmed',/**
 * @throws Throwable
 */ function () {
    $saleList = SaleListFactory::new()->create();
    $saleList->confirm();
    $res = $this->delete('/api/sale_lists/' . $saleList->id);
    $res->assertStatus(422);
});

test('product stores being utilized', function () {
    $products = Product::query()->get()->reduce(function ($carry, Product $product) {
        $carry[$product->id] = $product->toArray();
        return $carry;
    }, []);

    $saleList = SaleListFactory::new()->create();
    $saleList->saleItems()->each(function (SaleItem $saleItem) use (&$products) {
        $products[$saleItem->product_id]['store'] -= $saleItem->net_count;
    });

    foreach ($products as $product) {
        expect(Product::query()->find($product['id'])->store)->toEqual($product['store']);
    }
});


test('list can be unconfirmed', /**
 * @throws Throwable
 */function () {
    $user = User::factory()->inventoryAdmin()->createOne();
    Sanctum::actingAs($user);
    $oldBillsCount = Bill::count();
    $oldPaymentsCount = Payment::count();

    $saleList = SaleList::factory()->createOne();
    post("api/sale_lists/$saleList->id/un_confirm")->assertStatus(403);
    $user->givePermissionTo(PermissionEnum::UN_CONFIRM_SALE_LISTS);
    post("api/sale_lists/$saleList->id/un_confirm")->assertStatus(422);

    $saleList->confirm(true);
    expect(Bill::count())->toBe($oldBillsCount + 1)
        ->and(Payment::count())->toBe($oldPaymentsCount + 1);

    post("api/sale_lists/$saleList->id/un_confirm")->assertStatus(200);
    $saleList->refresh();
    expect(Bill::count())->toBe(Bill::count())
        ->and(Payment::count())->toBe($oldPaymentsCount)
        ->and($saleList->is_confirmed)->toBeFalse();
});
