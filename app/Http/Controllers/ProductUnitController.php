<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\ProductUnitResource;
use App\Models\ProductUnit;
use App\Http\Requests\StoreProductUnitRequest;
use App\Services\PaginatorService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductUnitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductUnit::class, 'product_unit');
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success(PaginatorService::from(ProductUnit::query(), ProductUnitResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder
                    ->allowedIncludes(['product', 'purchaseItems', 'saleItems'])
                    ->allowedSorts(['name', 'price', 'factor', 'type', 'is_active', 'is_default', 'is_visible_in_store'])
                    ->allowedFilters([
                        'product_id',
                        'name',
                        'store',
                        'is_active',
                        'is_default',
                        'is_visible_in_store',
                        'factor',
                        AllowedFilter::exact('type'),
                    ]);
            }));
    }

    public function store(StoreProductUnitRequest $request): JsonResponse
    {
        if ($request->validated('is_default')) {
            ProductUnit::query()->where('product_id', $request->validated('product_id'))->update(['is_default' => false]);
        }
        return ApiResponse::success(ProductUnitResource::make(ProductUnit::create($request->validated())));
    }

    public function show(ProductUnit $productUnit): JsonResponse
    {
        return ApiResponse::success(ProductUnitResource::make($productUnit));
    }

    public function update(StoreProductUnitRequest $request, ProductUnit $productUnit): JsonResponse
    {
        if ($request->validated('is_default')) {
            ProductUnit::query()->where('product_id', $request->validated('product_id'))->update(['is_default' => false]);
        }
        $productUnit->update($request->validated());
        return ApiResponse::success(ProductUnitResource::make($productUnit));
    }

    public function destroy(StoreProductUnitRequest $request, ProductUnit $productUnit): JsonResponse
    {
        return ApiResponse::success($productUnit->delete());
    }
}
