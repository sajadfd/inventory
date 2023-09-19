<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\SaleItemResource;
use App\Models\GlobalOption;
use App\Models\Product;
use App\Models\SaleItem;
use App\Http\Requests\StoreSaleItemRequest;
use App\Services\PaginatorService;
use App\Services\ProductStoreService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaleItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SaleItem::class, 'sale_item');
    }

    /**
     * @throws Exception
     */
    protected static function updateProductStoreFromUpdatedSaleItem(SaleItem $saleItem, $oldSaleItem): void
    {
        if ($saleItem->isDirty(['count', 'free_count', 'back_count'])) {
            $product = $saleItem->product;
            $newAmount = $saleItem->net_count - $oldSaleItem->net_count;
            if ($newAmount > 0) {
                ProductStoreService::UtilizeStoreInSale($product, $newAmount, $saleItem, $saleItem->productUnit);
            } else if ($newAmount < 0) {
                ProductStoreService::RefundSaleIntoStore($product, $newAmount, $saleItem, $saleItem->productUnit);
            }
        }
        $saleItem->save();
    }

    public function index(Request $request)
    {
        return ApiResponse::success(PaginatorService::from(
            SaleItem::query()->where('sale_list_id', $request->get('sale_list_id')),
            SaleItemResource::class,
            perPage: -1
        ));
    }

    /**
     * @throws Throwable
     */
    public function store(StoreSaleItemRequest $request)
    {
        $saleItem = SaleItem::where('sale_list_id', $request->validated('sale_list_id'))
            ->where('product_id', $request->validated('product_id'))
            ->latest('id')->first();

        if ($saleItem) {
            $oldSaleItem = $saleItem->replicate();
            $saleItem->fill([
                'count' => $saleItem->count + $request->validated('count')
            ]);
            self::updateProductStoreFromUpdatedSaleItem($saleItem, $oldSaleItem);
        } else {
            $product = Product::query()->find($request->get('product_id'));
            $saleItem = SaleItem::query()->create($request->validated() + [
                    'currency_value' => GlobalOption::GetCurrencyValue(),
                    'price' => $product->sale_price_in_iqd, // this will be excluded if price was determined in request
                ]);
            $saleItem->refresh();
            ProductStoreService::UtilizeStoreInSale($saleItem->product, $saleItem->net_count, $saleItem, $saleItem->productUnit);
        }

        DB::commit();
        return ApiResponse::success(SaleItemResource::make($saleItem));
    }

    public function show(SaleItem $saleItem)
    {
        return ApiResponse::success(SaleItemResource::make($saleItem));
    }

    /**
     * @throws Exception
     */
    public function update(StoreSaleItemRequest $request, SaleItem $saleItem)
    {
        $oldSaleItem = $saleItem->replicate();

        $saleItem->fill($request->validated());
        self::updateProductStoreFromUpdatedSaleItem($saleItem, $oldSaleItem);
        $saleItem->makeHidden(['saleList']);
        return ApiResponse::success(SaleItemResource::make($saleItem));
    }

    /**
     * @throws Exception
     */
    public function destroy(SaleItem $saleItem)
    {
        if ($saleItem->saleList->is_confirmed) {
            throw ValidationException::withMessages([__('List is confirmed, cannot delete')]);
        }
        ProductStoreService::RefundSaleIntoStore($saleItem->product, -$saleItem->net_count, $saleItem, $saleItem->productUnit);
        return ApiResponse::success($saleItem->delete());
    }
}
