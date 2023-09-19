<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\StockholderResource;
use App\Models\GlobalOption;
use App\Models\Stockholder;
use App\Http\Requests\StoreStockholderRequest;
use App\Services\PaginatorService;
use Spatie\QueryBuilder\QueryBuilder;

class StockholderController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Stockholder::class, 'stockholder');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Stockholder::query(), StockholderResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder
                ->allowedFilters([
                    'id', 'name', 'store_stocks', 'inventory_stocks',
                ])
                ->allowedSorts(['id', 'name', 'store_stocks', 'inventory_stocks',])
                ->defaultSort('name');

        }));
    }


    public function store(StoreStockholderRequest $request)
    {
        $stockholder = Stockholder::query()->create($request->validated());
        return ApiResponse::success($stockholder->toResource());
    }

    public function show(Stockholder $stockholder)
    {
        return ApiResponse::success($stockholder->toResource());
    }

    public function update(StoreStockholderRequest $request, Stockholder $stockholder)
    {
        $stockholder->update($request->validated());
        return ApiResponse::success($stockholder->toResource());
    }

    public function destroy(Stockholder $stockholder)
    {
        return ApiResponse::success($stockholder->delete());
    }
}
