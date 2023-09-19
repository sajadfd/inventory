<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\CarModelResource;
use App\Models\Car;
use App\Models\CarModel;
use App\Http\Requests\StoreCarModelRequest;
use App\Services\PaginatorService;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;

class CarModelController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CarModel::class,'car_model');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(CarModel::query(), CarModelResource::class));
    }

    public function store(StoreCarModelRequest $request)
    {
        $validated = $request->validated();
        (new UploadImageService)->saveAuto($validated);

        $carModel = CarModel::query()->create($validated);
        return ApiResponse::success(CarModelResource::make($carModel));

    }

    public function show(CarModel $carModel)
    {
        return ApiResponse::success(CarModelResource::make($carModel));
    }

    public function update(StoreCarModelRequest $request, CarModel $carModel)
    {
        $validated = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($validated);
        $carModel->update($validated);
        return ApiResponse::success(CarModelResource::make($carModel));
    }

    public function destroy(CarModel $carModel)
    {
        if ($carModel->cars()->exists()) {
            throw ValidationException::withMessages([__('This car model is used in some cars, can not be deleted')]);
        }
        return ApiResponse::success($carModel->delete());
    }
}
