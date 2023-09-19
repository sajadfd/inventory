<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\PurchaseItemResource;
use App\Models\GlobalOption;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Http\Requests\StorePurchaseItemRequest;
use App\Models\PurchaseList;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;

class PurchaseItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PurchaseItem::class, 'purchase_item');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(
            PurchaseItem::query()->where('purchase_list_id', request('purchase_list_id'))->with('product'),
            PurchaseItemResource::class,
            perPage: -1,
        ));
    }

    public function store(StorePurchaseItemRequest $request)
    {
        $purchaseItem = PurchaseItem::where('purchase_list_id', $request->validated('purchase_list_id'))
            ->where('product_id', $request->validated('product_id'))
            ->where('product_unit_id', $request->validated('product_unit_id'))
            ->where('price', $request->validated('price'))
            ->first();

        if ($purchaseItem) {
            $purchaseItem->count += $request->validated('count');
            $purchaseItem->save();
        } else {
            $purchaseItem = PurchaseItem::query()->create(
                \Arr::except($request->validated(), ['sale_price']) +
                ['currency_value' => GlobalOption::GetCurrencyValue(),]);
            $purchaseItem->refresh();
        }
        if ($request->has('sale_price')) {
            $product = Product::find($request->validated('product_id'));
            $product->update(['sale_price' => $request->validated('sale_price')]);
        }
        return ApiResponse::success(PurchaseItemResource::make($purchaseItem));
    }

    public function show(PurchaseItem $purchaseItem)
    {

        return ApiResponse::success(PurchaseItemResource::make($purchaseItem));
    }

    public function update(StorePurchaseItemRequest $request, PurchaseItem $purchaseItem)
    {
        $purchaseItem->update(\Arr::except($request->validated(), ['sale_price']));

        if ($request->has('sale_price')) {
            $product = $purchaseItem->product;
            $product->update(['sale_price' => $request->validated('sale_price')]);
        }

        return ApiResponse::success(PurchaseItemResource::make($purchaseItem));
    }

    public function destroy(PurchaseItem $purchaseItem)
    {
        if ($purchaseItem->purchaseList->is_confirmed) {
            throw ValidationException::withMessages([__('List is confirmed, cannot delete')]);
        }

        return ApiResponse::success($purchaseItem->delete());
    }
}
