<?php


use App\Enums\OrderStatusEnum;
use App\Enums\PermissionEnum;
use App\Enums\SaleType;
use App\Enums\UserType;
use App\Models\Car;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Diagnosis;
use App\Models\Product;
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
    $user = UserFactory::new(['type' => UserType::Customer])->create();
    Sanctum::actingAs($user);
});

test('test user of type customer get his cart ', function () {
    $response = get('/api/carts/show-current');

    $response->assertStatus(200);
});


test('test user of other type cannot get his cart ', function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs($user);
    $response = get('/api/carts/show-current');
    $response->assertStatus(403);
});

test('update current cart of store sale', function () {
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    $res = put('api/carts/update-current', ['notes' => 'notes', 'type' => SaleType::StoreSale->value]);
    $res->assertStatus(200);
    $cart = $customerUser->cart;
    expect($cart->type->value)->toBe(SaleType::StoreSale->value)
        ->and($cart->notes)->toBe('notes');
});

test('update current cart of inventory sale', function () {
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    $res = put('api/carts/update-current', ['notes' => 'notes',
        'type' => SaleType::InventorySale->value,
        'diagnosis_id' => Diagnosis::factory()->createOne()->id,
        'car_id' => Car::factory()->for($customerUser->customer)->createOne()->id,
    ]);
    $res->assertStatus(200);
    $cart = $customerUser->cart;
    expect($cart->type->value)->toBe(SaleType::InventorySale->value)
        ->and($cart->car()->exists())->toBeTrue()
        ->and($cart->diagnosis()->exists())->toBeTrue()
        ->and($cart->notes)->toBe('notes');
});

test('admin can get cart by user', function () {
    $cart = Cart::factory(1)->createOne();
    get('api/carts/user/' . $cart->user_id)->assertStatus(403);

    $user = UserFactory::new(['type' => UserType::Other])->create();
    $user->givePermissionTo(PermissionEnum::VIEW_USER_CARTS);
    Sanctum::actingAs($user);

    $res = get('api/carts/user/' . $cart->user_id);
    $res->assertStatus(200)
        ->assertJsonPath('data.id', $cart->id)
        ->assertJsonPath('data.user_id', $cart->user_id);
});

test('add item to current user cart', function () {
    $product = Product::factory()->createOne(['store' => 5,'is_visible_in_store'=>true]);
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);

    $res = post('api/cart_items', [
        'product_id' => $product->id,
        'count' => 2,
    ]);

    $res->assertStatus(200);

    $product->refresh();
    expect($product->store)->toBe(5.0)
        ->and($customerUser->cart->cartItems->count())->toBe(1)
        ->and($customerUser->cart->total_price)->toBe(2 * $product->sale_price_in_iqd);

});
test('add same item again to current user cart', function () {
    $product = Product::factory()->createOne(['store' => 5,'is_visible_in_store'=>true]);
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    post('api/cart_items', [
        'product_id' => $product->id,
        'count' => 2,
    ]);
    expect($customerUser->cart()->exists())->toBeTrue();
    $res = post('api/cart_items', [
        'product_id' => $product->id,
        'count' => 2,
    ]);

    $product->refresh();
    expect($product->store)->toBe(5.0)
        ->and($customerUser->cart?->cartItems->count())->toBe(1)
        ->and($customerUser->cart?->total_price)->toBe(4 * $product->sale_price_in_iqd);

});

test('add item to another user cart', function () {
    $product = Product::factory()->createOne(['store' => 5, 'is_visible_in_store' => true]);
    $customerUser = User::factory()->customer()->createOne();
    $user = User::factory()->inventoryAdmin()->createOne();
    Sanctum::actingAs($user);
    post('api/cart_items', ['user_id', $customerUser->id])->assertStatus(403);

    $user->givePermissionTo(PermissionEnum::CREATE_USER_CARTS_ITEMS);

    post('api/cart_items', [
        'product_id' => $product->id,
        'count' => 2,
        'user_id' => $customerUser->id,
    ])->assertStatus(200);
    expect($customerUser->cart->cartItems->count())->toBe(1);

});

test('update item to current user cart', function () {
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    $cart = $customerUser->cart()->firstOrCreate();
    $product = Product::factory()->createOne(['store' => 5, 'is_visible_in_store' => true]);
    $cartItem = CartItem::factory()->for($cart)->for($product)->createOne(['count' => 2]);

    $res = put('api/cart_items/' . $cartItem->id, [
        'product_id' => $product->id,
        'count' => 4,
    ]);

    $res->assertStatus(200);

    $product->refresh();
    $cart->refresh();
    $cartItem->refresh();
    expect($product->store)->toBe(5.0)
        ->and($customerUser->cart->cartItems()->count())->toBe(1)
        ->and($cartItem->count)->toBe(4.0)
        ->and($customerUser->cart->total_price)->toBe(4 * $product->sale_price_in_iqd);
});

test('delete cart item', function () {
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    $cart = $customerUser->cart()->firstOrCreate();
    $product = Product::factory()->createOne(['store' => 5, 'is_visible_in_store' => true]);
    $cartItem = CartItem::factory()->for($cart)->for($product)->createOne(['count' => 2]);

    delete('api/cart_items/' . $cartItem->id)
        ->assertStatus(200);

    $cart->refresh();
    expect($cart->cartItems()->count())->toBe(0);
});

test('test cart can be confirmed', function () {
    $customerUser = User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    $cart = $customerUser->cart()->firstOrCreate();
    $cartItem = CartItem::factory()->for($cart)->createOne();
    $total_price = $cart->total_price;
    $res = post('api/carts/confirm-current', []);
    $res->assertStatus(200);
    $order = $customerUser->customer->orders()->first();
    expect($customerUser->customer->orders()->count())->toBe(1)
        ->and($order->total_price)->toBe($total_price)
        ->and($order->status)->toBe(OrderStatusEnum::ConfirmedByCustomer);
});
