<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Http\Requests\StoreServiceRequest;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Service::class, 'service');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Service::query(),ServiceResource::class));
    }

    public function store(StoreServiceRequest $request)
    {
        $service = Service::query()->create($request->validated());
        return ApiResponse::success(ServiceResource::make($service));
    }

    public function show(Service $service)
    {
        return ApiResponse::success(ServiceResource::make($service));
    }

    public function update(StoreServiceRequest $request, Service $service)
    {
        $service->update($request->validated());
        return ApiResponse::success(ServiceResource::make($service));
    }

    public function destroy(Service $service)
    {
        if ($service->serviceItems()->exists()) {
            throw ValidationException::withMessages([__('This Service is used in inventory sale lists, can not be deleted')]);
        }
        return ApiResponse::success($service->delete());
    }
}
