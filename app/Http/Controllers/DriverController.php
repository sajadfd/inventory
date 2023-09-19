<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Http\ApiResponse;
use App\Services\PaginatorService;
use App\Http\Resources\DriverResource;
use App\Http\Requests\StoreDriverRequest;
use App\Services\UploadImageService;
use Spatie\QueryBuilder\QueryBuilder;

class DriverController extends Controller
{
    public function index()
    {
        $query = Driver::query()->withCount('orders')->with('user');
        return ApiResponse::success(PaginatorService::from($query, DriverResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters(['name', 'address', 'phone'])
                ->allowedSorts(['name', 'address', 'phone'])
                ->allowedIncludes(['orders']);
        }));
    }

    public function store(StoreDriverRequest $request)
    {
        $data = $request->validated();
        (new UploadImageService)->saveAuto($data);

        $driver = Driver::query()->create($data);
        return ApiResponse::success(DriverResource::make($driver));
    }

    public function show(Driver $driver)
    {
        $driver->load(['user'])->loadCount('orders');
        return ApiResponse::success(DriverResource::make($driver));
    }

    public function update(StoreDriverRequest $request, Driver $driver)
    {
        $data = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($data);

        $driver->update($data);

        if ($driver->user_id) {
            $driver->user?->profile?->update([
                'first_name' => $driver->name,
                'last_name' => '',
                'address' => $driver->address,
            ]);
            if ($driver->user?->phone) {
                $driver->update(['phone' => $driver->user->phone]);
            }
        }
        return ApiResponse::success(DriverResource::make($driver));
    }

    public function destroy(Driver $driver)
    {
        if ($driver->orders()->exists()) {
            return ApiResponse::error(__('This driver has orders, can not be deleted'));
        }
        return ApiResponse::success($driver->delete());
    }
}
