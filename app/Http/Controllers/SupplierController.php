<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\BillResource;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Services\PaginatorService;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Supplier::class, 'supplier');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Supplier::query(), SupplierResource::class));
    }


    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();
        (new UploadImageService)->saveAuto($data);

        $supplier = Supplier::query()->create($data);

        return ApiResponse::success(SupplierResource::make($supplier));
    }


    public function show(Supplier $supplier)
    {

        if (request()->boolean('with_details')) {
            $supplier->load('bills');
            //Unnecessary query, for future optimization
            $bills = PaginatorService::from($supplier->bills(), BillResource::class);
            $supplier->append(['debts', 'bills_total_price', 'bills_total_count', 'bills_un_payed_count']);
            $supplier->makeHidden('bills');
            return ApiResponse::success([
                'supplier' => SupplierResource::make($supplier),
                'bills' => $bills
            ]);
        }


        return ApiResponse::success(SupplierResource::make($supplier));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($data);

        $supplier->update($data);
        return ApiResponse::success(SupplierResource::make($supplier));
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseLists()->exists()) {
            throw ValidationException::withMessages([__('This supplier has purchase lists, can not be deleted')]);
        }
        return ApiResponse::success($supplier->delete());
    }
}
