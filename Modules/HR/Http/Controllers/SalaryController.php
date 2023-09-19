<?php

namespace Modules\HR\Http\Controllers;
ini_set('max_execution_time', 30);

use App\Enums\NotificationType;
use App\Enums\UserType;
use App\Models\GlobalOption;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Bonus;
use Modules\HR\Entities\Employer;
use Modules\HR\Entities\Salary;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Services\GlobalOptionsService;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Services\SalariesService;
use Modules\HR\Transformers\SalaryDataObject;
use Modules\HR\Transformers\SalaryResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SalaryController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Salary::class, 'salary');
    }

    public function index()
    {
        $paginator = PaginatorService::make(Salary::query()->with(['contract.employer']), SalaryResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                AllowedFilter::exact('contract_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('contract.track_by'),
                'contract.employer.name',
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('start_date', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('end_date', '<=', $value);
                }),
                AllowedFilter::callback('price_greater_or_equal', function ($query, $value) {
                    $query->where('price', '>=', $value);
                }),
                AllowedFilter::callback('price_less_or_equal', function ($query, $value) {
                    $query->where('price', '<=', $value);
                }),
            ])->allowedSorts(['start_date', 'end_date', 'contract_id', 'id', 'type', 'worked_days', 'worked_hours', 'is_payed'])
                ->defaultSort('-end_date')
                ->allowedIncludes(['absences', 'attendances', 'offDates', 'offWeekDays', 'bonuses', 'penalties']);
        });

        $totalPriceInIqd = $paginator->paginatorData->getCollection()->sum('price_in_iqd');

        $data = $paginator->proceed();
        $data['total_price_iqd'] = $totalPriceInIqd;

        return ApiResponse::success($data);
    }


    public function show(Salary $salary)
    {
        $salary->load(['contract.employer', 'absences', 'attendances', 'offDates', 'offWeekDays', 'bonuses', 'penalties']);
        return ApiResponse::success(SalaryResource::make($salary));
    }

    public function destroy(Salary $salary)
    {
        if ($salary->is_payed === true) {
            throw ValidationException::withMessages([__('Salary is paid, cannot delete')]);
        }
        return ApiResponse::success($salary->delete());
    }

    public function pay(Request $request, Salary $salary)
    {
        $this->authorize('pay', $salary);
        if ($salary->is_payed) {
            throw ValidationException::withMessages([__('Already payed')]);
        }
        if ($salary->price < 0) {
            throw ValidationException::withMessages([__('Can not pay price less than zero')]);
        }
        $request->validate(['notes' => 'string']);

        $salary->update([
            'payed_at' => now(),
            'currency_value' => GlobalOptionsService::GetCurrencyValue(),
            'is_payed' => true,
            'notes' => $request->input('notes'),
        ]);
        $salary->load('contract.employer');
        return ApiResponse::success(SalaryResource::make($salary));
    }

    public function payMany(Request $request)
    {
        $this->authorize('pay', new Salary());
        $request->validate([
            'ids' => ['required', 'array'],
            'notes' => ['string'],
        ]);

        /** @var Collection $salaries */
        $salaries = Salary::query()->where('is_payed', false)->findMany($request->input('ids'));
        if ($salaries->where('price', '<', 0)->first()) {
            throw ValidationException::withMessages([__('Can not pay price less than zero')]);
        }
        $salaries->each(function ($salary) use ($request) {
            $salary->update([
                'payed_at' => now(),
                'currency_value' => GlobalOptionsService::GetCurrencyValue(),
                'is_payed' => true,
                'notes' => $request->input('notes'),
            ]);
        });

        $salaries->load('contract.employer');
        $response = SalaryResource::collection($salaries);
        return ApiResponse::success(['data' => $response, 'total_price_iqd' => $salaries->sum('price_in_iqd')]);
    }

    /**
     * @throws \Throwable
     */
    public function calculate()
    {
        $this->authorize('calculate', new Salary());

        $totalCount = 0;
        $totalPrice = 0;

        DB::transaction(function () use (&$totalCount, &$totalPrice) {
            $unifyUnpaidSalaries = GlobalOptionsService::UnifyUnpaidSalaries();
            //Those relations can be improved to get only dates that didn't get consumed in other salaries by price or hours
            $employers = Employer::where('is_active', true)->withWhereHas('activeContract')
                ->with(['activeContract.absences', 'activeContract.attendances', 'activeContract.offDates', 'activeContract.offWeekDays', 'activeContract.salaries',
                    'activeContract.bonuses', 'activeContract.penalties']);

            $employers->each(function (Employer $employer) use (&$totalPrice, &$totalCount, $unifyUnpaidSalaries) {
                $contract = $employer->activeContract;
                if ($unifyUnpaidSalaries) {
                    $contract->salaries()->where('is_payed', false)->delete();
                }
                $salaries = $contract->calculateDues($unifyUnpaidSalaries);
                $salaries->each(function (SalaryDataObject $salaryObject) use (&$totalPrice, &$totalCount) {
                    $salary = $salaryObject->store();
                    $totalCount++;
                    $totalPrice += $salary->price;
                });
            });
        });

        if ($totalCount > 0) {
            //Notify Super Admin
            User::query()
                ->where('type', UserType::SuperAdmin)
                ->each(fn(User $user) => $user->notify(__("Salary awaiting payment"), __(":count salaries for your employers awaiting payment", ['count' => $totalCount]), NotificationType::SalaryIssued));
        }

        return ApiResponse::success($totalCount);
    }

}
