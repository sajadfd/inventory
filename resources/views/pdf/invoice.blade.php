@extends('layouts.app-pdf')
@section('title')
    {{__($reportTitle ?? 'Statistics')}}
@endsection

@php
    use Illuminate\Support\Carbon;
    use App\Models\User;

    $squ = 0;
    $totalcount = 0;
    $totalprice = 0 ;
    $totalpriceservice = 0 ;
    foreach($saleList->saleItems as $item){
        $totalcount += $item->count ;
        $totalprice += $item->total_price;
    }
    foreach($saleList->serviceItems as $service){
        $totalpriceservice += $service->price;
    }

@endphp

@section('content')
<style>
        .table1 , .table2  , .table4 , .table5 , .table6{
            margin-left: auto;
            margin-right: auto;
        }
        .table4 , .table4 tr , th , .table4 td , .table6 , .table6 tr , .table6 td  {
            border: solid 2px rgb(90, 90, 90);
        }

        .table4 tr td {
            text-align: center;
            width: 300px;
            height: 50px;
        }

        .table4 tr th , .body5{
            text-align: center;
            background-color: rgb(197, 122, 99);
            width: 300px;
            height:50px;
        }

        table , tr , td {
            border-bottom: solid white 1px;
            border-collapse: collapse;
        }
        .table2 , .table5 {
            margin-top: 10px;
        } 

        .table4{
            margin-top:15px;
        }

        .table6{
            margin-top: 25px;
        }

        .title1 , .title2 , .title3 , .title5 {
            background-color: rgb(223, 223, 223);
            text-align: center;
        }
        .body1 , .body2 {
            background-color: gray;
            text-align: right;
        }
        .title1 , .body1 , .title5{
            width: 200px;
            height: 50px;
        }
        .title2{
            width:150px;
            height: 50px;
        }
        .body2{
            width: 407px;
            height: 50px;
        }

        .table6 tr th{
            background-color: gray;
            width: 250px;
            height: 50px;
            text-align: center;
        }
        .table6 tr td{
            width: 250px;
            height: 50px;
            text-align: center;
        }
    </style>

    @if($InventoryType == true)
        <table class="table1" style="font-size: 14px">
            <tr>
                <td class="title1">حضرة السيد</td>
                <td class="body1">{{$saleList->car->customer_name}}</td>

                <td class="title1">نوع السيارة</td>
                <td class="body1">{{$saleList->car->car_type_name}}</td>

                <td class="title1">رقم العداد</td>
                <td class="body1">{{$saleList->car->meter_number}}</td>
            </tr>

            <tr>
                <td class="title1">رقم الهاتف</td>
                <td class="body1">{{$saleList->car->customer->phone}}</td>

                <td class="title1">الموديل</td>
                <td class="body1">{{$saleList->car->car_model_name}}</td>

                <td class="title1">رقم الشاصي</td>
                <td class="body1">{{$saleList->car->vin}}</td>
            </tr>

            <tr>
                <td class="title1">رقم السيارة</td>
                <td class="body1">{{$saleList->car->plate_number}}</td>
            </tr>
        </table>
    @else
        <table class="table1" style="font-size: 14px">
            <tr>
                <td class="title1">حضرة السيد</td>
                <td class="body1">{{$saleList->customer->name}}</td>

            </tr>
            <tr>
                <td class="title1">رقم الهاتف</td>
                <td class="body1">{{$saleList->customer->phone}}</td>

            </tr>
        </table>
    @endif
    <table class="table2" style="font-size: 14px">
        <tr>
            @if($InventoryType)
                <td class="title2">مسؤول الصيانة</td>
                <td class="body2" style="border-left: solid white 90px;">{{$saleList->mechanic?->name}}</td>
            @endif

            <td class="title2" rowspan="2">رمز الفاتورة</td>
            <td class="body2" rowspan="2"><img src="data:image/svg+xml;base64,{{base64_encode($qrCodeSvg)}}"
                                               alt="{{$saleList->bill->code}}" srcset=""
                                               style="width: 100px; height: 100px;">
            </td>
        </tr>

        <tr>
            <td class="title2">التاريخ</td>
            <td class="body2"
                style="border-left: solid white 90px;direction: ltr">{{$saleList->date->format('Y-m-d h:mA')}}</td>
        </tr>
    </table>

    <h4 style="margin-bottom: 5px">{{__('Products')}}:</h4>
    <table class="table4" style="font-size: 20px">
        <tr>
            <th>#</th>
            <th>المادة</th>
            <th>الكمية</th>
            <th>سعر القطعة</th>
            <th>الاجمالي</th>
        </tr>

        @foreach($saleList->saleItems as $item)
            <tr >
                <td>{{$item->id}}</td>
                <td>{{$item->product->name}}</td>
                <td>{{$item->count}}</td>
                <td>{{number_format($item->price)}}</td>
                <td>{{number_format($item->total_price)}} {{__($saleList->currency)}}</td>
            </tr>
        @endforeach
    </table>

    <table class="table5">
        <tr>
            <td class="title5">اجمالي الكميات</td>
            <td class="body5">{{$totalcount}}</td>

            <td class="title5">المبلغ الاجمالي</td>
            <td class="body5"
                style="border-left: solid white 90px;">{{number_format($item->total_price)}} {{__($saleList->currency)}}</td>
        </tr>
    </table>

    @if($InventoryType)
        <h4 style="margin-top: 25px;margin-bottom: 0px">{{__('Repair')}}:</h4>
        <table class="table6" >
            <tr>
                <th>#</th>
                <th>تفاصيل الخدمة</th>
                <th>سعر الخدمة</th>
            </tr>

            @foreach($saleList->serviceItems as $service)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{$service->service->name}}</td>
                    <td>{{number_format($service->total_price)}} {{__($saleList->currency)}}</td>
                </tr>
            @endforeach
            <tr>
                <td style="background-color: rgb(202, 196, 189);">اجمالي مبالغ الخدمة</td>
                <td colspan="2" style="background-color: grey;font-weight: bold">{{number_format($saleList->service_items_total_price)}} {{__($saleList->currency)}}</td>
            </tr>
        </table>
    @endif
@endsection
