<?php

namespace App\Http\Controllers;

use App\Enums\ProductTransactionEnum;
use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\GlobalOption;
use App\Models\InitialStore;
use App\Models\Product;
use App\Services\PaginatorService;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index()
    {

        if (request()->has('brand_id')) {
            $productsQuery = Product::query()->when(request()->has('brand_id'), function ($q) {
                $q->where('brand_id', request()->input('brand_id'));
            });
        } else {
            $productsQuery = Product::query()->when(request()->has('category_id'), function ($q) {
                $q->where('category_id', request()->input('category_id'));
            });
        }

        if (!($user = auth()->user()) || $user->type === UserType::Customer) {
            $productsQuery->where('is_visible_in_store', true);
        }

        return ApiResponse::success(PaginatorService::from($productsQuery, ProductResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    'name',
                    AllowedFilter::exact('barcode'),
                    AllowedFilter::exact('product_location_id'),
                    AllowedFilter::exact('id'),
                    AllowedFilter::exact('brand_id'),
                    AllowedFilter::exact('category_id'),
                ])
                    ->allowedIncludes([
                        'transactions'
                    ])
                    ->allowedSorts(['name', 'product_location_id', 'brand_id', 'category_id', 'store', 'created_at'])
                    ->defaultSort('name');
            }));
    }

    public function show(Product $product)
    {
        return ApiResponse::success(ProductResource::make($product));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        (new UploadImageService)->saveAuto($data);

        $product = Product::query()->create(\Arr::except($data, ['initial_purchase_price']));

        if ($product->store != 0) {
            $initialStore = $product->initialStore()->create(
                [
                    'count' => $product->store,
                    'price' => $data['initial_purchase_price'],
                    'currency_value' => GlobalOption::GetCurrencyValue(),
                ]
            );
            $product->transactions()->create([
                'count' => $initialStore->count,
                'type' => ProductTransactionEnum::Initial,
                'targetable_id' => $initialStore->id,
                'targetable_type' => InitialStore::class,
            ]);
        }

        $product = Product::query()->find($product->id); //To load relations.
        return ApiResponse::success(ProductResource::make($product));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $data = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($data);

        $product->fill(\Arr::except($data, ['initial_purchase_price']));

        if ($request->validated('initial_purchase_price')) {
            $initialStore = $product->initialStore;
            $initialStore->fill([
                'price' => $data['initial_purchase_price']
            ]);
            if ($initialStore->isDirty('price')) {
                $initialStore->fill(['currency_value' => GlobalOption::GetCurrencyValue()]);
            }
            $initialStore->save();
        }

        $product->save();

        $product->refresh();
        return ApiResponse::success(ProductResource::make($product));
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists() || $product->cartItems()->exists() || $product->orderItems()->exists()) {
            throw ValidationException::withMessages([__('This Product has purchases or sales, can not be deleted')]);
        }
        $product->initialStore()->delete();
        $product->transactions()->delete();
        return ApiResponse::success($product->delete());
    }
}
