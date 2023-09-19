<?php

namespace Modules\HR\Http\Controllers;

use Modules\HR\Entities\Loan;
use Modules\HR\Http\Requests\LoanRequest;
use Modules\HR\Transformers\LoanResource;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Services\PaginatorService;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Penalty;
use Modules\HR\Services\GlobalOptionsService;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Loan::class, 'loan');
    }

    public function index()
    {
        $paginator = PaginatorService::from(Loan::query()->with(['contract.employer']), LoanResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                AllowedFilter::exact('id'),
                'contract.employer.name',
                AllowedFilter::exact('contract_id'),
                AllowedFilter::exact('contract.employer.id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('date', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('date', '<=', $value);
                })
            ])->allowedIncludes(['installments']);
        });

        return ApiResponse::success($paginator);
    }

    public function store(LoanRequest $request)
    {
        try {
            $loan = Loan::query()->create($request->validated() + [
                'currency_value' => GlobalOptionsService::GetCurrencyValue()
            ]);

            if ($loan) (new Loan)->createInstallments($loan);

            $loan->load('contract.employer');

            return ApiResponse::success(LoanResource::make($loan));
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '22003')
                return ApiResponse::error('The provided values is out of range. Please provide a valid value.');
            throw $exception;
        }
    }

    public function show(Loan $loan)
    {
        $loan->load('contract.employer');

        return ApiResponse::success(LoanResource::make($loan));
    }

    public function update(LoanRequest $request, Loan $loan)
    {
        try {
            $loan->update($request->validated() + [
                'currency_value' => GlobalOptionsService::GetCurrencyValue()
            ]);
            //TODO: update Installments
            $loan->load('contract.employer');

            return ApiResponse::success(LoanResource::make($loan));
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '22003')
                return ApiResponse::error('The provided values is out of range. Please provide a valid value.');
            throw $exception;
        }
    }
    public function loanInstallments(Loan $loan)
    {
        $loans = Loan::with(['contract.employer', 'installments'])->where('id', $loan->id)->get();
        $groupedData = [];
        foreach ($loans as $loan) {
            $contract = $loan['contract']->toArray();
            $loanData = $loan->toArray();

            if (!array_key_exists('loan', $contract)) {
                $contract['loan'] = $loanData;
                $contract['loan']['installments'] = [];
                unset($contract['loan']['contract']);
                $groupedData[$contract['id']] = ['contract' => $contract];
            }
            $groupedData[$contract['id']]['contract']['loan']['installments'][] = $loanData['installments'];
        }
         return ApiResponse::success( array_values($groupedData)[0]);
    }

    public function destroy(Loan $loan)
    {
        if ($loan->installments()->where('due_date', '<', now())->exists()) {
            throw ValidationException::withMessages([__('Record is used, cannot delete')]);
        }
        $penaltyIds = $loan->installments()->pluck('penalty_id')->toArray();
        Penalty::whereIn('id', $penaltyIds)->delete();
        foreach ($loan->installments as $installment) {
            $installment->installmentPayments()->delete();
        }
        $loan->installments()->delete();
        return ApiResponse::success($loan->delete());
    }
}
