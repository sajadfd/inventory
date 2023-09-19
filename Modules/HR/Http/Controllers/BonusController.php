<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Bonus;
use Modules\HR\Entities\Contract;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\StoreBonusRequest;
use Modules\HR\Services\GlobalOptionsService;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\BonusResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BonusController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bonus::class, 'bonus');
    }

    public function index()
    {
        $paginator = PaginatorService::from(Bonus::query()->with(['contract.employer']), BonusResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
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

    public function store(StoreBonusRequest $request)
    {
        $contract = Contract::find($request->input('contract_id'));
        $bonus = Bonus::query()->create($request->validated() + [
                'currency' => $contract->salary_currency,
                'currency_value' => GlobalOptionsService::GetCurrencyValue()
            ]);
        $bonus->load('contract.employer');
        return ApiResponse::success(BonusResource::make($bonus));
    }

    public function show(Bonus $bonus)
    {
        $bonus->load('contract.employer');
        return ApiResponse::success(BonusResource::make($bonus));
    }

    public function update(StoreBonusRequest $request, Bonus $bonus)
    {
        $bonus->update($request->validated());
        $bonus->load('contract.employer');
        return ApiResponse::success(BonusResource::make($bonus));
    }

    public function destroy(Bonus $bonus)
    {
        if ($bonus->salary()->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        }
        return ApiResponse::success($bonus->delete());
    }
}
