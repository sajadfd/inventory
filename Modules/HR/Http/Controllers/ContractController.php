<?php

namespace Modules\HR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Contract;
use Modules\HR\Http\Requests\StoreContractRequest;
use Modules\HR\Policies\ContractPolicy;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\ContractResource;
use Modules\HR\Http\ApiResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ContractController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Contract::class, 'contract');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Contract::query()->with('employer'), ContractResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                'salary_price', 'day_work_hours', 'day_work_start_hour', 'notes',
                AllowedFilter::exact('track_by'),
                AllowedFilter::exact('salary_type'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('start_date', '<=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('end_date', '<=', $value);
                }),
            ]);
        }));
    }

    public function store(StoreContractRequest $request)
    {
        $data = $request->validated();
        $contract = Contract::query()->create($data);
        $contract->load('employer');
        return ApiResponse::success(ContractResource::make($contract));
    }

    public function show(Request $request, Contract $contract)
    {
        if (($filterFrom = $request->date('filter_from')) && ($filterTo = $request->date('filter_to'))) {
            foreach (['absences', 'attendances', 'offDates', 'salaries'] as $relation) {
                $contract->load([$relation => function ($q) use ($filterFrom, $filterTo) {
                    $q->whereBetween('start_date', [$filterFrom, $filterTo]);
                }]);
            }
            foreach (['bonuses', 'penalties'] as $relation) {
                $contract->load([$relation => function ($q) use ($filterFrom, $filterTo) {
                    $q->whereDate('date', '>=', $filterFrom)->whereDate('date', '<=', $filterTo);
                }]);
            }
            $contract->load('offWeekDays');
        } else {
            $contract->load(Contract::$defaultRelations);
        }
        $contract->load('employer');
        return ApiResponse::success(ContractResource::make($contract));
    }

    public function update(StoreContractRequest $request, Contract $contract)
    {
        if ($contract->start_date->format('Y-m-d') < now()->format('Y-m-d')
            || $contract->hasUses()) {
            throw ValidationException::withMessages([__('Cannot update')]);
        }

        $data = $request->validated();
        $contract->update($data);

        $contract->load('employer');

        return ApiResponse::success(ContractResource::make($contract));
    }

    public function destroy(Contract $contract)
    {
        if ($contract->hasUses() || $contract->start_date->diffInDays(now()) > 1) {
            throw ValidationException::withMessages([__('Cannot delete')]);
        }
        return ApiResponse::success($contract->delete());
    }

    public function deactivate(Contract $contract)
    {
        $this->authorize('deactivate', $contract);
        $contract->is_active = false;
        $contract->save();
        return ApiResponse::success($contract);
    }

    public function previewDues(Request $request, Contract $contract)
    {
        $this->authorize('previewDues', $contract);
        $asOne = $request->boolean('as_one', false);
        $salaries = $contract->calculateDues($asOne);
        return ApiResponse::success(['dues' => $salaries->toArray(), 'total' => $salaries->sum('price')]);
    }

}
