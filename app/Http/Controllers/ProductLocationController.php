<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\ProductLocationResource;
use App\Models\ProductLocation;
use App\Http\Requests\StoreProductLocationRequest;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class ProductLocationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductLocation::class, 'product_location');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(ProductLocation::query(), ProductLocationResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedIncludes(['products'])
                    ->allowedSorts(['id', 'name', 'is_active'])
                    ->defaultSort('name')
                    ->allowedFilters(['id','name', 'is_active'])
                    ->allowedIncludes(['products']);
            }));
    }

    public function store(StoreProductLocationRequest $request)
    {
        return ApiResponse::success(ProductLocationResource::make(ProductLocation::create($request->validated())));
    }

    public function show(ProductLocation $productLocation)
    {
        return ApiResponse::success(ProductLocationResource::make($productLocation->load('products')));
    }

    public function update(StoreProductLocationRequest $request, ProductLocation $productLocation)
    {
        return ApiResponse::success(ProductLocationResource::make(tap($productLocation)->update($request->validated())));
    }

    public function destroy(ProductLocation $productLocation)
    {
        if ($productLocation->products()->exists()) {
            throw ValidationException::withMessages([__('Record has products, cannot delete')]);
        }
        return ApiResponse::success($productLocation->delete());
    }
}
