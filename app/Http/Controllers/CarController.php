<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Http\Requests\StoreCarRequest;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CarController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Car::class, 'car');
    }

    public function index()
    {
        $carsQuery = Car::query()->basicRelations();

        if (auth()->user()->type === UserType::Customer) {
            $carsQuery->where('customer_id', auth()->user()->customer?->id);
        } else if ($customer_id = request('customer_id')) {
            $carsQuery->where('customer_id', $customer_id);
        }

        return ApiResponse::success(PaginatorService::from($carsQuery, CarResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    AllowedFilter::exact('customer_id'),
                    AllowedFilter::exact('car_type_id'),
                    AllowedFilter::exact('car_model_id'),
                    AllowedFilter::exact('color_id'),
                    'plate_number',
                    'model_year',
                    'vin',
                    'meter_number',
                    'is_active',
                    'notes',
                ])->allowedSorts([
                    'id',
                    'customer_id',
                    'car_type_id',
                    'car_model_id',
                    'color_id',
                    'plate_number',
                    'model_year',
                    'vin',
                    'meter_number',
                    'is_active',
                    'notes',
                    'created_at'
                ]);
            }));
    }

    public function store(StoreCarRequest $request)
    {
        $car = Car::query()->create($request->validated());
        return ApiResponse::success(CarResource::make($car));
    }

    public function show(Car $car)
    {
        return ApiResponse::success(CarResource::make($car));
    }

    public function update(StoreCarRequest $request, Car $car)
    {
        $car->update($request->validated());
        return ApiResponse::success(CarResource::make($car));
    }

    public function destroy(Car $car)
    {
        if ($car->saleLists()->exists() || $car->carts()->exists() || $car->orders()->exists()) {
            throw ValidationException::withMessages([__('This car is used, can not be deleted')]);
        }
        return ApiResponse::success($car->delete());
    }
}
