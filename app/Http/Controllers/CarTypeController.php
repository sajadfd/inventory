<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\CarTypeResource;
use App\Models\CarType;
use App\Http\Requests\StoreCarTypeRequest;
use App\Services\PaginatorService;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;

class CarTypeController extends Controller
{

    public function __construct()
    {
         $this->authorizeResource(CarType::class,'car_type');
    }
    public function index()
    {
        return ApiResponse::success(PaginatorService::from(CarType::query(), CarTypeResource::class));
    }

    public function store(StoreCarTypeRequest $request)
    {
        $validated = $request->validated();
        (new UploadImageService)->saveAuto($validated);

        $carModel = CarType::query()->create($validated);
        return ApiResponse::success(CarTypeResource::make($carModel));
    }

    public function show(CarType $carType)
    {
        return ApiResponse::success(CarTypeResource::make($carType));
    }

    public function update(StoreCarTypeRequest $request, CarType $carType)
    {
        $validated = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($validated);
        $carType->update($validated);
        return ApiResponse::success(CarTypeResource::make($carType));
    }

    public function destroy(CarType $carType)
    {
        if ($carType->cars()->exists()) {
            throw ValidationException::withMessages([__('This car type is used in some cars, can not be deleted')]);
        }
        return ApiResponse::success($carType->delete());
    }
}
