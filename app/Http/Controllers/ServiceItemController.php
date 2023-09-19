<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\ServiceItemResource;
use App\Models\GlobalOption;
use App\Models\ServiceItem;
use App\Http\Requests\StoreServiceItemRequest;
use App\Services\PaginatorService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ServiceItem::class, 'service_item');
    }

    public function index(Request $request)
    {
        return ApiResponse::success(PaginatorService::from(
            ServiceItem::query()->where('sale_list_id', $request->get('sale_list_id')),
            ServiceItemResource::class,
            perPage: -1
        ));
    }

    public function store(StoreServiceItemRequest $request)
    {
        $serviceItem = ServiceItem::query()->create($request->validated() + [
                'currency_value' => GlobalOption::GetCurrencyValue(),
            ]);
        $serviceItem->refresh();
        return ApiResponse::success(ServiceItemResource::make($serviceItem));
    }

    public function show(ServiceItem $serviceItem)
    {
        return ApiResponse::success(ServiceItemResource::make($serviceItem));
    }

    public function update(StoreServiceItemRequest $request, ServiceItem $serviceItem)
    {
        $serviceItem->update($request->validated());
        return ApiResponse::success(ServiceItemResource::make($serviceItem));

    }

    public function destroy(ServiceItem $serviceItem)
    {
        if ($serviceItem->saleList->is_confirmed) {
            throw ValidationException::withMessages([__('List is confirmed, cannot delete')]);
        }
        return ApiResponse::success($serviceItem->delete());
    }
}
