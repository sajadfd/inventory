<?php

namespace Modules\HR\Http\Controllers;

use App\Services\OrWhereQueryBuilderFilter;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Transformers\EmployerResource;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Http\Requests\StoreEmployerRequest;
use Modules\HR\Entities\Employer;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployerController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Employer::class, 'employer');
    }

    public function index()
    {
        $paginator = new PaginatorService(Employer::query()->with('activeContract'), EmployerResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                AllowedFilter::custom('name', new OrWhereQueryBuilderFilter()),
                AllowedFilter::custom('phone', new OrWhereQueryBuilderFilter())
            ]);
        });
        return ApiResponse::success($paginator->proceed());
    }


    public function store(StoreEmployerRequest $request)
    {
        $data = $request->validated();
        (new UploadImageService)->saveAuto($data);
        $employer = Employer::query()->create($data);
        return ApiResponse::success(EmployerResource::make($employer));
    }

    public function show(Employer $employer)
    {
        $employer->load('activeContract');
        return ApiResponse::success(EmployerResource::make($employer));
    }

    public function update(StoreEmployerRequest $request, Employer $employer)
    {
        $data = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($data);
        $employer->update($data);
        return ApiResponse::success(EmployerResource::make($employer));
    }


    public function destroy(Employer $employer)
    {
        if ($employer->contracts()->exists()) {
            throw ValidationException::withMessages([__('This employer has contracts, cannot be deleted')]);
        }
        return ApiResponse::success($employer->delete());
    }
}
