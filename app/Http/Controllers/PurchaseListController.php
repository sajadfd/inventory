<?php

namespace App\Http\Controllers;

use App\Enums\SaleType;
use App\Http\ApiResponse;
use App\Http\Resources\PurchaseListResource;
use App\Models\PurchaseList;
use App\Http\Requests\StorePurchaseListRequest;
use App\Models\SaleList;
use App\Services\GeneratePDFService;
use App\Services\PaginatorService;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PurchaseListController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchaseList::class, 'purchase_list');
    }

    public function index()
    {
        $purchaseListsQuery = PurchaseList::query()
            ->when($supplier_id = request('supplier_id'), fn($q) => $q->where('supplier_id', $supplier_id))
            ->with(['supplier', 'purchaseItems', 'purchaseItems.product', 'bill', 'bill.payments', 'bill.billable', 'bill.billable.person'])
            ->orderByDesc('date');
        if (request()->has('start_date')) {
            $purchaseListsQuery
                ->whereDate('date', '>=', $startDate = request('start_date'))
                ->whereDate('date', '<=', request('end_date', $startDate));
        }
        return ApiResponse::success(PaginatorService::from(
            $purchaseListsQuery,
            transformer: fn(PurchaseList $purchaseList) => new PurchaseListResource($purchaseList, false),
        ));
    }

    public function store(StorePurchaseListRequest $request)
    {
        $purchaseList = PurchaseList::query()->create($request->validated());
        $purchaseList->refresh();
        return ApiResponse::success(PurchaseListResource::make($purchaseList));
    }

    public function show(PurchaseList $purchaseList)
    {
        if ($asPdf = request()->boolean('as-pdf', false) && $purchaseList->is_confirmed) {
            $qrCodeSvg = QrCode::size(120)->generate(
                $purchaseList->bill->code,
            );
            $paperSize = 'A4';
            $list = $purchaseList;
            $viewHtml = view('pdf.list', compact('list', 'qrCodeSvg', 'asPdf', 'paperSize'));
            return GeneratePDFService::generate($viewHtml, $paperSize)->download('purchase_list_' . $purchaseList->id . '.pdf');
        }
        return ApiResponse::success(PurchaseListResource::make($purchaseList));
    }


    public function update(StorePurchaseListRequest $request, PurchaseList $purchaseList)
    {
        $purchaseList->update($request->validated());

        $purchaseList->save();

        return ApiResponse::success(PurchaseListResource::make($purchaseList));
    }

    public function destroy(PurchaseList $purchaseList)
    {
        if ($purchaseList->is_confirmed) {
            throw ValidationException::withMessages([__('List is confirmed, cannot delete')]);
        }
        $purchaseList->purchaseItems()->delete();
        return ApiResponse::success($purchaseList->delete());
    }

    public function confirm(PurchaseList $purchaseList)
    {
        $this->authorize('confirm', $purchaseList);
        $autoPay = request()->boolean('auto_pay');
        if ($autoPay) {
            $this->authorize('autoPay', $purchaseList);
        }
        $purchaseList->confirm($autoPay);
        return ApiResponse::success(PurchaseListResource::make($purchaseList));
    }

    /**
     * @throws \Throwable
     */
    public function unConfirm(PurchaseList $purchaseList)
    {
        $this->authorize('unConfirm', $purchaseList);
        $purchaseList->unConfirm();
        $purchaseList->refresh();
        return ApiResponse::success(PurchaseListResource::make($purchaseList));
    }
}
