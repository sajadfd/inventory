@extends('layouts.app-pdf')
@section('title')
    {{__($reportTitle ?? 'Statistics')}}
@endsection

@section('content')
<style>
       h1{
            font-family:cursive;
            text-align: center;
            background-color:  rgb(197, 122, 99);
            width: 30%;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 0px;
       }
       .title{
           background-color: rgb(165, 162, 157);
           width:150px;
           text-align: center;
           height: 60px;
           font-weight: bold;
       }
       .body{
           background-color:rgb(219, 219, 219) ;
           width:150px;
           text-align: center;
           height: 60px;
       }
       table{
          border-collapse: collapse;
          margin-left: auto;
          margin-right: auto;
       }
       tr{
           border-bottom: 2px solid white;
       }
       img{
        width: 150px;
        height: 150px;
       }
    </style>

   @if($isPurchase)
   <h1>سند دفع</h1>
   @else
   <h1>سند قبض</h1>
   @endif
   <table>
        <tr>
          @if($isPurchase)
           <td class="title">اسم المورد</td>
          @else
          <td class="title">اسم الزبون</td>
          @endif
           <td class="body">{{$payment->bill->person_name}}</td>
           <td class="title">التاريخ</td>
           <td class="body">{{$payment->payed_at}}</td>  
        </tr>

        <tr>
           <td class="title">المبلغ الكلي للقائمة</td>
           <td class="body">{{number_format($payment->bill->total_price)}} {{__($payment->currency)}}</td>
           <td class="title">المتبقي سابقا</td>
           <td class="body">{{number_format($payment->bill->getRemainingPriceAttribute())}} {{__($payment->currency)}}</td>  
        </tr>

        <tr>
           <td class="title">المدفوع حاليا</td>
           <td class="body">{{number_format($payment->bill->getPayedPriceAttribute())}} {{__($payment->currency)}}</td>
           <td class="title">المتبقي الجديد</td>
           <td class="body">{{number_format($payment->bill->getRemainingPriceAttribute() - $payment->bill->getPayedPriceAttribute())}} {{__($payment->currency)}}</td>
        </tr>

        <tr>
           <td class="title" colspan="2">الديون الأخرى</td>
           <td class="body" colspan="2">{{$payment->bill->getPersonDebtsAttribute() - ($payment->bill->getRemainingPriceAttribute() - $payment->bill->getPayedPriceAttribute())}} {{__($payment->currency)}}</td>
        </tr>

        <tr>
           <td class="title" colspan="2">كود الفاتورة </td>
           <td class="title" colspan="2">كود السند</td>
        </tr>
        <tr>
           <td class="body bill" colspan="2"><img src="data:image/svg+xml;base64,{{base64_encode($qrCodeSvg_bill)}}" alt="{{$payment->bill->code}}"></td>
           <td class="body pay" colspan="2"><img src="data:image/svg+xml;base64,{{base64_encode($qrCodeSvg_pay)}}" alt="{{$payment->code}}"></td>
        </tr>
   </table>
@endsection