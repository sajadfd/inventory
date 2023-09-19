<?php

namespace Modules\HR\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\HR\Entities\Installment;
use Modules\HR\Entities\InstallmentPayment;
use Modules\HR\Http\ApiResponse;
use Modules\HR\Http\Requests\InstallmentPaymentRequest;
use Modules\HR\Services\GlobalOptionsService;
use Modules\HR\Services\PaginatorService;
use Modules\HR\Transformers\InstallmentPaymentResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class InstallmentPaymentController extends Controller
{
    public function index()
    {
        $paginator = PaginatorService::from(InstallmentPayment::query()->with(['installment.loan.contract.employer']), InstallmentPaymentResource::class, useQueryBuilder: function (QueryBuilder $queryBuilder) {
            $queryBuilder->allowedFilters([
                'installment.loan.contract.employer.name',
                AllowedFilter::exact('installment.loan.contract_id'),
                AllowedFilter::exact('installment.loan.contract.employer.id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('payed_at', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('payed_at', '<=', $value);
                })
            ]);
        });

        return ApiResponse::success($paginator);
    }

    public function store(InstallmentPaymentRequest $request)
    {
        $installment = Installment::find($request->validated('installment_id'));
        $installmentPayment = InstallmentPayment::query()->create($request->validated() + [
                'currency' => $installment->loan->currency,
                'currency_value' => GlobalOptionsService::GetCurrencyValue()
            ]);

        $installmentPayment->load('installment.loan.contract.employer');

        return ApiResponse::success(InstallmentPaymentResource::make($installmentPayment));
    }

    public function show(InstallmentPayment $installmentPayment)
    {
        $installmentPayment->load('installment.loan.contract.employer');

        return ApiResponse::success(InstallmentPaymentResource::make($installmentPayment));
    }

    public function update(InstallmentPaymentRequest $request, InstallmentPayment $installmentPayment)
    {
        $installment = Installment::find($request->validated('installment_id'));
        $installmentPayment->update($request->validated() + [
                'currency' => $installment->loan->currency,
                'currency_value' => GlobalOptionsService::GetCurrencyValue()
            ]);

        $installmentPayment->load('installment.loan.contract.employer');

        return ApiResponse::success(InstallmentPaymentResource::make($installmentPayment));
    }

    public function destroy(InstallmentPayment $installmentPayment)
    {
        $createdTime = Carbon::parse($installmentPayment->created_at);
        if (!$createdTime->addDay()->greaterThan(Carbon::now())) {
            throw ValidationException::withMessages([__('An installment payment cannot be deleted after one day of being paid')]);
        }

        return ApiResponse::success($installmentPayment->delete());
    }
}
