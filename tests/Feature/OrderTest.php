<?php

use App\Enums\OrderStatusEnum;
use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\BrandSeeder;
use Database\Seeders\OrderSeeder;
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

test('index of admin', function () {
    seed([OrderSeeder::class]);
    $res = get('/api/orders');
    $res->assertJsonPath('data.total', Order::count());
});

test('index of admin with index', function () {
    seed([OrderSeeder::class]);
    Order::first()->confirm();
    $res = get('/api/orders?filter[status]=confirmed_by_admin');
    $res->assertStatus(200);
    $res->assertJsonPath('data.total', Order::where('status',OrderStatusEnum::ConfirmedByAdmin)->count());
});

test('index of customer', function () {
    $customerUsers = User::factory(2)->customer()->create();
    seed([OrderSeeder::class]);
    Sanctum::actingAs($customerUsers[1]);
    $res = get('/api/orders');
    $res->assertJsonPath('data.total', $customerUsers[1]->customer->orders()->count());
});

test('unconfirmed order can be canceled by admin', function () {
    seed([ProductSeeder::class]);
    $customerUser = User::factory()->createOne(['type' => UserType::Customer]);
    $order = Order::factory()->for($customerUser->customer)->createOne();
    for ($i = 0; $i < 3; $i++) {
        OrderItem::factory()->for($order)->create();
    }

    $productStores = Product::pluck('store', 'id');
    $res = put("api/orders/{$order->id}/cancel", ['cancellation_reason' => '1234']);
    $res->assertStatus(200);

    $order->refresh();
    expect($order->status->value)->toBe(OrderStatusEnum::CanceledByAdmin->value);

    $order->orderItems->groupBy('product_id')->each(function ($orderItems, $product_id) use ($productStores) {
        expect(Product::find($product_id)->store)
            ->toBe($productStores[$product_id] + $orderItems->sum('count'));
    });
    expect(SaleList::count())->toBe(0);
});

test('unconfirmed order can be canceled by customer', function () {
    $customerUser = User::factory()->createOne(['type' => UserType::Customer]);
    $order = Order::factory()->for($customerUser->customer)->createOne();
    Sanctum::actingAs($customerUser);
    User::first()->givePermissionTo(PermissionEnum::VIEW_CUSTOMERS_ORDERS);
    put("api/orders/{$order->id}/cancel", ['cancellation_reason' => '1234'])->assertStatus(200);
    $order->refresh();
    expect($order->status->value)->toBe(OrderStatusEnum::CanceledByCustomer->value);
});

test('order can be confirmed', function () {
    $driverUser = User::factory()->driver()->createOne();
    $customerUser = User::factory()->customer()->createOne();
    $order = Order::factory()->storeOrder(true, true)->createOne();
    $res = put("api/orders/{$order->id}/confirm");
    $res->assertStatus(200);

    $order->refresh();
    $saleList = $order->saleList;
    expect(SaleList::count())->toBe(1)
        ->and($saleList->saleItems()->count())->toBe($order->orderItems()->count())
        ->and($saleList->sale_items_total_pieces)->toBe(+$order->orderItems()->sum('count'))
        ->and($saleList->total_price)->toBe($order->total_price)
        ->and($saleList->bill->payed_price)->toBe(0)
        ->and($order->orderItems->sum(fn($item) => $item->transactions->count()))->toBe(0)
        ->and($order->status)->toBe(OrderStatusEnum::ConfirmedByAdmin)
        ->and($order->customer->user->notifications()->count())->toBe(1)
        ->and($driverUser->notifications()->count())->toBe(1);
});

test('order can not be confirmed twice', function () {
    $order = Order::factory()->createOne();
    $order->confirm();
    put("api/orders/{$order->id}/confirm")->assertStatus(422);
});

test('order can be finished', function () {
    $customerUser = User::factory()->customer()->createOne();
    $order = Order::factory()->storeOrder()->createOne();
    put("api/orders/{$order->id}/finish")->assertStatus(422);

    $order->confirm();
    $res = put("api/orders/{$order->id}/finish");
    $res->assertStatus(200);
    $order->refresh();
    expect($customerUser->notifications()->count())->toBe(1)
    ->and($order->status->value)->toBe(OrderStatusEnum::Finished->value);

});
