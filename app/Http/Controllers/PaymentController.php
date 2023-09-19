<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\Bill;
use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\PurchaseList;
use App\Services\GeneratePDFService;
use App\Services\PaginatorService;
use QrCode;

class PaymentController extends Controller
{

    public function __construct()
    {
         $this->authorizeResource(Payment::class,'payment');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(
            Payment::query()->where('bill_id', request('bill_id')),
            transformer: fn(Payment $payment) => $payment->toResource(false)));
    }

   /* public function store(StorePaymentRequest $request)
    {
        $bill = Bill::query()->find($request->get('bill_id'));
        $payment = $bill->payments()->create($request->validated() + [
                'currency' => $bill->currency,
                'currency_value' => $bill->currency_value,
                'payed_at' => now(),
                'received_by' => auth()->id(),
            ]);

        if ($bill->payments()->sum('price') === $bill->total_price) {
            $bill->update(['is_payed' => true]);
        }

        return ApiResponse::success($payment->toResource());
    }*/

    public function show(Payment $payment)
    {
        $isPurchase = false;
        $design = request()->query('design');
        if ($asPdf = request()->boolean('as-pdf', false)) {

            if($payment->bill->billable_type === PurchaseList::class){
                $isPurchase = true; // مورد
            }
            $qrCodeSvg_bill = QrCode::size(120)->generate(
                $payment->bill->code,
            );

            $qrCodeSvg_pay = QrCode::size(120)->generate(
                $payment->code,
            );
            if($design == 1){
                $viewHtml = view('pdf.payment_1', compact('payment', 'qrCodeSvg_bill', 'qrCodeSvg_pay' , 'asPdf' , 'isPurchase'));
            }
            else{
                $viewHtml = view('pdf.payment_2', compact('payment', 'qrCodeSvg_bill', 'qrCodeSvg_pay' , 'asPdf' , 'isPurchase'));
            }
            $pdf = GeneratePDFService::generate($viewHtml,'A4');
            return $pdf->download('payment.pdf');
        }
        return ApiResponse::success($payment->toResource());
    }


  /*  public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());
        return ApiResponse::success($payment->toResource());
    }*/
    /*
            public function destroy(Payment $payment)
            {
                return ApiResponse::success($payment->delete());
            //TODO Return Bill is_payed to false
            }*/
}
