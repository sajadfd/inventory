<?php

namespace App\Http\Controllers;

use App\Enums\SaleType;
use App\Http\ApiResponse;
use App\Http\Resources\SaleListResource;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Http\Requests\StoreSaleListRequest;
use App\Services\GeneratePDFService;
use App\Services\PaginatorService;
use App\Services\ProductStoreService;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SaleListController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(SaleList::class, 'sale_list');
    }

    public function index()
    {
        $saleListsQuery = SaleList::query()
            ->when(in_array(request('sale_type'), SaleType::getAllValues()), function ($query) {
                $query->where('type', request('sale_type'));
            })
            ->when($customer_id = request('customer_id'), fn($q) => $q->where('customer_id', $customer_id))
            ->when(request('sale_type') === SaleType::InventorySale->value, function ($query) {
                $query->with(['mechanic', 'diagnosis', 'car', 'car.customer', 'car.carType', 'car.carModel', 'car.color', 'car.carType', 'serviceItems', 'serviceItems.service']);
            })
            ->with(['customer', 'bill', 'saleItems', 'saleItems.product', 'bill.payments', 'bill.billable', 'bill.billable.person'])
            ->orderByDesc('date');

        if (request()->has('start_date')) {
            $saleListsQuery
                ->whereDate('date', '>=', $startDate = request('start_date'))
                ->whereDate('date', '<=', request('end_date', $startDate));
        }

        return ApiResponse::success(PaginatorService::from(
            $saleListsQuery,
            transformer: fn(SaleList $saleList) => new SaleListResource($saleList, false),
            useQueryBuilder: function (QueryBuilder $queryBuilder) {
                $queryBuilder->allowedFilters([
                    AllowedFilter::exact('customer_id'),
                    'customer.name',
                    'car.plate_number',
                    'car.vin',
                    'date',
                    'is_confirmed',
                    'diagnosis',
                ])->allowedSorts(['is_confirmed', 'date', 'customer_id', 'diagnosis_id',]);
            }
        ));
    }

    public function store(StoreSaleListRequest $request)
    {
        $saleList = SaleList::query()->create($request->validated());
        $saleList->refresh();
        return ApiResponse::success(SaleListResource::make($saleList));
    }

    public function show(SaleList $saleList)
    {
        if ($asPdf = request()->boolean('as-pdf', false)) {
            $qrCodeSvg = $saleList->bill ? QrCode::size(120)->generate(
                $saleList->bill->code,
            ) : null;
            $paperSize = 'A4';
            $list = $saleList;
            $viewHtml = view('pdf.list', compact('list', 'qrCodeSvg', 'asPdf', 'paperSize'));
            return GeneratePDFService::generate($viewHtml, $paperSize)->download('sale_list_' . $saleList->id . '.pdf');

        }
        return ApiResponse::success(SaleListResource::make($saleList));
    }

    public function update(StoreSaleListRequest $request, SaleList $saleList)
    {
        $saleList->update($request->validated());
        return ApiResponse::success(SaleListResource::make($saleList));
    }

    /**
     * @throws \Exception
     */
    public function destroy(SaleList $saleList)
    {
        if ($saleList->is_confirmed) {
            throw ValidationException::withMessages([__('List is confirmed, cannot delete')]);
        }
        $saleList->saleItems->each(function (SaleItem $saleItem) {
            ProductStoreService::RefundSaleIntoStore($saleItem->product, -$saleItem->net_count, $saleItem, $saleItem->productUnit);
            $saleItem->delete();
        });
        $saleList->serviceItems()->delete();
        return ApiResponse::success($saleList->delete());
    }

    /**
     * @throws \Throwable
     */
    public function confirm(SaleList $saleList)
    {
        $this->authorize('confirm', $saleList);
        $autoPay = request()->boolean('auto_pay');
        if ($autoPay) {
            $this->authorize('autoPay', $saleList);
        }
        $saleList->confirm($autoPay);
        $saleList->refresh();
        return ApiResponse::success($saleList->toResource());
    }

    /**
     * @throws \Throwable
     */
    public function unConfirm(SaleList $saleList)
    {
        $this->authorize('unConfirm', $saleList);
        $saleList->unConfirm();
        $saleList->refresh();
        return ApiResponse::success($saleList->toResource());
    }

}
