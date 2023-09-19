<?php
declare(strict_types=1);

namespace Modules\HR\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\StoreManyOffDateRequest;
use Modules\HR\Http\Requests\StoreOffDateRequest;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\OffDateResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OffDateController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(OffDate::class, 'off_date');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(OffDate::query()->with('contract.employer'),
            OffDateResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    'contract.employer.name',
                    AllowedFilter::exact('contract.id'),
                    AllowedFilter::exact('contract.employer.id'),
                    AllowedFilter::exact('consider_as_attendance'),
                    AllowedFilter::callback('start_date', function ($query, $value) {
                        $query->where('start_date', '>=', $value);
                    }),
                    AllowedFilter::callback('end_date', function ($query, $value) {
                        $query->where('end_date', '<=', $value);
                    })
                ]);
            }));
    }

    public function store(StoreOffDateRequest $request)
    {
        $data = $request->validated();
        $offDate = OffDate::query()->create($data);
        $offDate->load('contract.employer');
        return ApiResponse::success(OffDateResource::make($offDate));
    }

    public function storeMany(StoreManyOffDateRequest $request)
    {
        $data = $request->validated();
        Contract::query()->isNotEnded()
            ->when(!empty($request->contract_ids), fn($query) => $query->whereIn('id', $request->contract_ids))
            ->each(function (Contract $contract) use ($data) {
                OffDate::query()->create($data + [
                        'contract_id' => $contract->id
                    ]);
            });
        return ApiResponse::success(true);
    }

    public function show(OffDate $offDate)
    {
        $offDate->load('contract.employer');
        return ApiResponse::success(OffDateResource::make($offDate));
    }

    public function update(StoreOffDateRequest $request, OffDate $offDate)
    {
        $data = $request->validated();
        $offDate->update($data);
        $offDate->load('contract.employer');
        return ApiResponse::success(OffDateResource::make($offDate));
    }

    public function destroy(OffDate $offDate)
    {
        if ($offDate->salaries()->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        }
        return ApiResponse::success($offDate->delete());
    }
}
