<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Penalty;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\StorePenaltyRequest;
use Modules\HR\Services\GlobalOptionsService;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\PenaltyResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PenaltyController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Penalty::class, 'penalty');
    }

    public function index()
    {

        $paginator = PaginatorService::from(Penalty::query()->with('contract.employer')->with('salaries'), PenaltyResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                'contract.employer.name',
                AllowedFilter::exact('contract_id'),
                AllowedFilter::exact('contract.employer.id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('date', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('date', '<=', $value);
                })
            ]);
        });
        return ApiResponse::success($paginator);
    }

    public function store(StorePenaltyRequest $request)
    {
        $contract = Contract::find($request->input('contract_id'));
        $penalty = Penalty::query()->create($request->validated() + [
                'currency' => $contract->salary_currency,
                'currency_value' => GlobalOptionsService::GetCurrencyValue()
            ]);
        $penalty->load('contract.employer', 'salaries');
        return ApiResponse::success(PenaltyResource::make($penalty));

    }

    public function show(Penalty $penalty)
    {
        $penalty->load('contract.employer', 'salaries');
        return ApiResponse::success(PenaltyResource::make($penalty));
    }

    public function update(StorePenaltyRequest $request, Penalty $penalty)
    {
        $penalty->update($request->validated());
        $penalty->load('contract.employer', 'salaries');
        return ApiResponse::success(PenaltyResource::make($penalty));

    }

    public function destroy(Penalty $penalty)
    {
        if ($penalty->salaries()->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        } else if ($penalty->installment()->exists()) {
            throw ValidationException::withMessages([__('Penalty is for an installment, cannot delete')]);
        }
        return ApiResponse::success($penalty->delete());
    }
}
