<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Absence;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\StoreAbsenceRequest;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\AbsenceResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AbsenceController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Absence::class, 'absence');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Absence::query()->with('contract.employer'),
            AbsenceResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder
                    ->allowedSorts(['end_date', 'start_date'])
                    ->defaultSort('-end_date')
                    ->allowedFilters([
                        'contract.employer.name',
                        AllowedFilter::exact('contract.id'),
                        AllowedFilter::exact('contract.employer.id'),
                        AllowedFilter::callback('start_date', function ($query, $value) {
                            $query->where('start_date', '>=', $value);
                        }),
                        AllowedFilter::callback('end_date', function ($query, $value) {
                            $query->where('end_date', '<=', $value);
                        })
                    ]);
            }));
    }

    public function store(StoreAbsenceRequest $request)
    {
        $data = $request->validated();
        $absence = Absence::query()->create($data);
        $absence->load('contract.employer');
        return ApiResponse::success(AbsenceResource::make($absence));
    }

    public function show(Absence $absence)
    {
        $absence->load('contract.employer');
        return ApiResponse::success(AbsenceResource::make($absence));
    }

    public function update(StoreAbsenceRequest $request, Absence $absence)
    {
        $data = $request->validated();
        $absence->update($data);
        $absence->load('contract.employer');
        return ApiResponse::success(AbsenceResource::make($absence));
    }

    public function destroy(Absence $absence)
    {
        if ($absence->salaries()->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        }
        return ApiResponse::success($absence->delete());
    }
}
