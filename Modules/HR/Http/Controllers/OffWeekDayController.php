<?php
declare(strict_types=1);

namespace Modules\HR\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffWeekDay;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\StoreManyOffWeekDayRequest;
use Modules\HR\Http\Requests\StoreOffWeekDayRequest;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\OffWeekDayResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OffWeekDayController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(OffWeekDay::class, 'off_week_day');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(OffWeekDay::query()->with('contract.employer'),
            OffWeekDayResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    'contract.employer.name',
                    AllowedFilter::exact('contract.id'),
                    AllowedFilter::exact('contract.employer.id'),
                    AllowedFilter::exact('day'),
                    AllowedFilter::exact('consider_as_attendance'),
                ]);
            }));
    }

    public function store(StoreOffWeekDayRequest $request)
    {
        $data = $request->validated();
        $offWeekDay = OffWeekDay::query()->create($data);
        $offWeekDay->load('contract.employer');
        return ApiResponse::success(OffWeekDayResource::make($offWeekDay));
    }

    public function storeMany(StoreManyOffWeekDayRequest $request)
    {
        $data = $request->validated();
        Contract::query()->isNotEnded()
            ->when(!empty($request->contract_ids), fn($query) => $query->whereIn('id', $request->contract_ids))
            ->each(function (Contract $contract) use ($data) {
                OffWeekDay::query()->create(Arr::except($data, 'contract_ids') + [
                        'contract_id' => $contract->id
                    ]);
            });
        return ApiResponse::success(true);
    }


    public function show(OffWeekDay $offWeekDay)
    {
        $offWeekDay->load('contract.employer');
        return ApiResponse::success(OffWeekDayResource::make($offWeekDay));
    }

    public function update(StoreManyOffWeekDayRequest $request, OffWeekDay $offWeekDay)
    {
        $data = $request->validated();
        $offWeekDay->update($data);
        $offWeekDay->load('contract.employer');
        return ApiResponse::success(OffWeekDayResource::make($offWeekDay));
    }

    public function destroy(OffWeekDay $offWeekDay)
    {
        if ($offWeekDay->salaries()->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        }
        return ApiResponse::success($offWeekDay->delete());
    }
}
