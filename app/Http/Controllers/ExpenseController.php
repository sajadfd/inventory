<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\GlobalOption;
use App\Services\PaginatorService;
use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Expense::class, 'expense');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Expense::query(), ExpenseResource::class,
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    'description',
                    AllowedFilter::exact('source'),
                    AllowedFilter::callback('start_date', function ($query, $value) {
                        $query->where('date', '>=', $value);
                    }),
                    AllowedFilter::callback('end_date', function ($query, $value) {
                        $query->where('date', '<=', $value);
                    }),
                ]);
            }));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated() + [
                'currency' => 'iqd',
                'currency_value' => GlobalOption::GetCurrencyValue()
            ];
        $expense = Expense::query()->create($data);
        return ApiResponse::success(ExpenseResource::make($expense));
    }

    public function show(Expense $expense)
    {
        return ApiResponse::success(ExpenseResource::make($expense));
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        $data = $request->validated() + [
                'currency' => 'iqd',
                'currency_value' => GlobalOption::GetCurrencyValue()
            ];
        $expense->update($data);
        return ApiResponse::success(ExpenseResource::make($expense));
    }

    public function destroy(Expense $expense)
    {
        return ApiResponse::success($expense->delete());
    }
}
