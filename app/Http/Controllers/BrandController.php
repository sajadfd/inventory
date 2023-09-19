<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Http\Requests\StoreBrandRequest;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class BrandController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Brand::class, 'brand');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Brand::query(), BrandResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    'name'
                ]);
            }));
    }


    public function store(StoreBrandRequest $request)
    {
        $brand = Brand::query()->create($request->validated());
        return ApiResponse::success(BrandResource::make($brand));
    }


    public function show(Brand $brand)
    {
        return ApiResponse::success(BrandResource::make($brand));
    }

    public function update(StoreBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        return ApiResponse::success(BrandResource::make($brand));
    }


    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            throw ValidationException::withMessages([__('Brand used in products, cannot be deleted')]);
        }

        return ApiResponse::success($brand->delete());
    }
}
