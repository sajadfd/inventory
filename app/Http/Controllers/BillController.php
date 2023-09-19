<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Requests\PayToBillRequest;
use App\Http\Resources\BillResource;
use App\Http\Resources\PaymentResource;
use App\Models\Bill;
use App\Services\GeneratePDFService;
use App\Services\PaginatorService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BillController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Bill::class, 'bill');
    }

    public function index()
    {
        $paginator = new PaginatorService(Bill::query()->with('payments'), BillResource::class);
        return ApiResponse::success($paginator->proceed());
    }

    public function showByCode(string $code)
    {
        $bill = Bill::query()->where('code', $code)->firstOrFail();
        $this->authorize('view', $bill);
        return $this->show($bill);
    }

    public function show(Bill $bill)
    {
        if (request()->boolean('as-pdf', true)) {
            $saleList = $bill->list;
            $qrCodeSvg = QrCode::size(120)->generate(
                $bill->code,
            );
            $asPdf = true;
            $paperSize = 'A5';
            $viewHtml = view('pdf.bill', compact('saleList', 'bill', 'qrCodeSvg', 'asPdf', 'paperSize'));
            $pdf = GeneratePDFService::generate($viewHtml, $paperSize);

            return $pdf->download('bill.pdf');
        } else {
            return BillResource::make($bill);
        }
    }

    public function pay(PayToBillRequest $request, Bill $bill)
    {
        $this->authorize('pay',$bill);
        $payment = $bill->pay($request->price, $request->notes);
        $bill->load('payments');
        return ApiResponse::success(['payment' => PaymentResource::make($payment), 'bill' => BillResource::make($bill)]);
    }

}
