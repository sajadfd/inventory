<?php

namespace Modules\HR\Http\Controllers;

use Modules\HR\Entities\Installment;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\InstallmentRequest;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\InstallmentResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class InstallmentController extends Controller
{
    public function index()
    {
        $paginator = PaginatorService::from(Installment::query()->with(['loan.contract.employer']), InstallmentResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                'loan.contract.employer.name',
                AllowedFilter::exact('loan.contract_id'),
                AllowedFilter::exact('loan.contract.employer.id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('due_date', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('due_date', '<=', $value);
                })
            ]);
        });

        return ApiResponse::success($paginator);
    }

    public function show(Installment $installment)
    {
        $installment->load('loan.contract.employer');

        return ApiResponse::success(InstallmentResource::make($installment));
    }

    public function update(InstallmentRequest $request, Installment $installment)
    {
        $installment->update($request->validated());

        $installment->load('loan.contract.employer');

        return ApiResponse::success(InstallmentResource::make($installment));
    }
}
