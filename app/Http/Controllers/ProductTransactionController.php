<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\ProductTransactionResource;
use App\Models\ProductTransaction;
use App\Services\PaginatorService;
use Illuminate\Http\Request;

class ProductTransactionController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(ProductTransaction::class,'product_transaction');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(
            ProductTransaction::query()->where('product_id', request('product_id')),
            ProductTransactionResource::class,
        ));
    }

    public function show(ProductTransaction $productTransaction)
    {
        return ProductTransactionResource::make($productTransaction);
    }

}
