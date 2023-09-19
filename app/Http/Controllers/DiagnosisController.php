<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Requests\StoreDiagnosisRequest;
use App\Http\Resources\DiagnosisResource;
use App\Models\Diagnosis;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;

class DiagnosisController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Diagnosis::class, 'diagnosis');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Diagnosis::query(), DiagnosisResource::class));

    }

    public function store(StoreDiagnosisRequest $request)
    {
        $diagnosis = Diagnosis::query()->create($request->validated());
        return ApiResponse::success(DiagnosisResource::make($diagnosis));
    }

    public function show(Diagnosis $diagnosis)
    {
        return ApiResponse::success(DiagnosisResource::make($diagnosis));
    }

    public function update(StoreDiagnosisRequest $request, Diagnosis $diagnosis)
    {
        $diagnosis->update($request->validated());
        return ApiResponse::success(DiagnosisResource::make($diagnosis));
    }

    public function destroy(Diagnosis $diagnosis)
    {
        if ($diagnosis->saleLists()->exists() || $diagnosis->orders()->exists() || $diagnosis->carts()->exists()) {
            throw ValidationException::withMessages([__('This diagnosis is used, can not be deleted')]);
        }

        return ApiResponse::success($diagnosis->delete());
    }

}
