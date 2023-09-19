<?php

namespace App\Http\Controllers;

use App\Enums\CartTypeEnum;
use App\Enums\NotificationType;
use App\Enums\OrderStatusEnum;
use App\Enums\PermissionEnum;
use App\Http\ApiResponse;
use App\Http\Requests\ConfirmCurrentRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductStoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{

    public function updateCurrent(UpdateCartRequest $request)
    {
        $this->authorize('updateCurrent', new Cart);
        $cart = auth()->user()->cart()->firstOrCreate();
        $cart->update($request->validated());
        return ApiResponse::success(CartResource::make($cart));
    }

    public function showCurrent()
    {
        $this->authorize('showCurrent', new Cart);
        $user = Auth()->user();
        $cart = $user->cart()->firstOrCreate();
        return ApiResponse::success(CartResource::make($cart));
    }


    public function getCartByUser(User $user)
    {
        $this->authorize('getCartByUser', new Cart);
        $cart = $user->cart;
        $cart->load(['user', 'user.customer']);
        return ApiResponse::success(CartResource::make($cart));
    }

    public function confirmCurrent(ConfirmCurrentRequest $request)
    {
        $this->authorize('confirm', new Cart);
        $order = null;
        \DB::transaction(function () use ($request, &$order) {
            $cart = Auth::user()->cart()->firstOrCreate();
            $cartItems = $cart->cartItems->load('product');

            if ($cart->type === CartTypeEnum::StoreSale && $cartItems->isEmpty()) {
                throw ValidationException::withMessages([__('Cart is empty')]);
            } else if ($cart->type === CartTypeEnum::InventorySale && $cart->car()->doesntExist()) {
                throw ValidationException::withMessages([__('Inventory order can not be placed without a car')]);
            }

            if ($insufficientCartItem = $cartItems->first(fn(CartItem $cartItem) => $cartItem->count > $cartItem->product->store)) {
                throw ValidationException::withMessages([__(
                    'Product :product has maximum store of :count',
                    ['product' => $insufficientCartItem->product->name, 'count' => $insufficientCartItem->product->store]
                )]);
            }
            $order = Order::create([
                    'type' => $cart->type,
                    'notes' => $cart->notes,
                    'car_id' => $cart->car_id,
                    'diagnosis_id' => $cart->diagnosis_id,
                    'status' => OrderStatusEnum::ConfirmedByCustomer,
                    'customer_id' => $cart->user->customer->id,
                ] + $request->validated());

            $orderItemArray = [];

            foreach ($cartItems as $cartItem) {
                $orderItemArray[] = [
                    'price' => $price = $cartItem->product->sale_price_in_iqd,
                    'count' => $count = $cartItem->count,
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_unit_id' => $cartItem->product_unit_id,
                ];
            }

            $order->orderItems()->createMany($orderItemArray)->each(function (OrderItem $orderItem) {
                ProductStoreService::UtilizeStoreInSale($orderItem->product, $orderItem->count, $orderItem, $orderItem->productUnit);
            });

            $cart->cartItems()->delete();
            $cart->delete();

            User::query()->whereHas('permissions', fn($q) => $q->whereName(PermissionEnum::VIEW_CUSTOMERS_ORDERS))
                ->each(fn(User $user) => $user->notify(__('New order!'), __('New order from customer :customer', ['customer' => $order->customer->name]), NotificationType::OrderRequested));

        });

        if (!$order) {
            return ApiResponse::error([__('Error Occurred')]);
        }
        return ApiResponse::success(OrderResource::make($order));
    }
}
