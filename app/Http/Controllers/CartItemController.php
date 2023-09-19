<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\CartItem;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;

class CartItemController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(CartItem::class, 'cart_item');
    }

    public function store(StoreCartItemRequest $request)
    {

        $validated=$request->validated();
        $cartItem = CartItem::where('cart_id', $validated['cart_id'])
            ->where('product_id', $validated['product_id'])->first();
//        dump($request->input('cart_id'));
        if ($cartItem) {
            $cartItem->fill([
                'count' => $cartItem->count + $validated['count']
            ]);
            $cartItem->save();
        } else {
            $cartItem = CartItem::create($request->validated());
        }
        return ApiResponse::success(CartItemResource::make($cartItem));
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        $cartItem->update($request->validated());
        return ApiResponse::success(CartItemResource::make($cartItem));
    }

    public function destroy(CartItem $cartItem)
    {
        return ApiResponse::success($cartItem->delete());
    }

}
