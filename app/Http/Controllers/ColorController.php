<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use App\Http\Requests\StoreColorRequest;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;

class ColorController extends Controller
{
    public function __construct()
    {
         $this->authorizeResource(Color::class,'color');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Color::query(), ColorResource::class));
    }

    public function store(StoreColorRequest $request)
    {
        $color = Color::query()->create($request->validated());
        return ApiResponse::success(ColorResource::make($color));
    }

    public function show(Color $color)
    {
        return ApiResponse::success(ColorResource::make($color));
    }

    public function update(StoreColorRequest $request, Color $color)
    {
        $color->update($request->validated());
        return ApiResponse::success(ColorResource::make($color));
    }

    public function destroy(Color $color)
    {
        if ($color->cars()->exists()) {
            throw ValidationException::withMessages([__('This color is used in some cars, can not be deleted')]);
        }
        return ApiResponse::success($color->delete());
    }
}
