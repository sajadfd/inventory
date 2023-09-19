<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Services\PaginatorService;
use App\Models\Mechanic;
use App\Http\Requests\StoreMechanicRequest;
use App\Http\Requests\UpdateMechanicRequest;
use App\Http\Resources\MechanicResource;
use Illuminate\Validation\ValidationException;

class MechanicController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Mechanic::class, 'mechanic');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Mechanic::query(), MechanicResource::class));
    }

    public function store(StoreMechanicRequest $request)
    {
        $mechanic = Mechanic::query()->create($request->validated());
        return ApiResponse::success(MechanicResource::make($mechanic));
    }

    public function show(Mechanic $mechanic)
    {
        return ApiResponse::success(MechanicResource::make($mechanic));
    }

    public function update(StoreMechanicRequest $request, Mechanic $mechanic)
    {
        $mechanic->update($request->validated());
        return ApiResponse::success(MechanicResource::make($mechanic));
    }

    public function destroy(Mechanic $mechanic)
    {
        if ($mechanic->salelists()->exists()) {
            throw ValidationException::withMessages([__('This mechanic has lists, can not be deleted')]);
        }
        return ApiResponse::success($mechanic->delete());
    }
}
