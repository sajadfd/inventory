<?php

namespace App\Http\Controllers;

use App\Models\StockholderWithdraw;
use App\Http\Requests\StoreStockholderWithdrawRequest;
use App\Http\Requests\UpdateStockholderWithdrawRequest;
use App\Http\ApiResponse;
use App\Http\Resources\StockholderWithdrawResource;
use App\Services\PaginatorService;
use Spatie\QueryBuilder\QueryBuilder;

class StockholderWithdrawController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(StockholderWithdraw::class, 'stockholderwithdraw');
    } 
    
    public function index()
    {
        return ApiResponse::success(PaginatorService::from(StockholderWithdraw::query(), StockholderWithdrawResource::class,
        useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                'stockholder_id '
            ]);
        }));
    }

    public function create()
    {
        //
    }

    public function store(StoreStockholderWithdrawRequest $request)
    {
        $stockholderWithdraw = StockholderWithdraw::query()->create($request->validated());
        return ApiResponse::success(StockholderWithdrawResource::make($stockholderWithdraw));
    }

    public function show(StockholderWithdraw $stockholderWithdraw)
    {
        return ApiResponse::success(StockholderWithdrawResource::make($stockholderWithdraw));

    }

    public function edit(StockholderWithdraw $stockholderWithdraw)
    {
        //
    }

    public function update(UpdateStockholderWithdrawRequest $request, StockholderWithdraw $stockholderWithdraw)
    {
        $stockholderWithdraw->update($request->validated());
        return ApiResponse::success(StockholderWithdrawResource::make($stockholderWithdraw));
    }

    public function destroy(StockholderWithdraw $stockholderWithdraw)
    {
        return ApiResponse::success($stockholderWithdraw->delete());
    }
}




 
 
    

  
   

 
 
