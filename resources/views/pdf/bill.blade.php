@extends('layouts.app-pdf')
@section('title')
    {{__($reportTitle ?? 'Bill')}}
@endsection
@section('content')

    @php
        use App\Models\GlobalOption;
        use App\Enums\GlobalOptionEnum;
        use App\Models\PurchaseList;
        use App\Models\SaleList;
    @endphp
    <div style="padding: 5mm;">
        <img style="float:left" src="data:image/svg+xml;base64,{{base64_encode($qrCodeSvg)}}"
             alt="{{$bill->code}}">
        <div style="margin-bottom: 5mm;background: yellow;">
            <span>{{__($bill->type_title)}} {{__('for')}}: </span>
            <span style="font-weight: bold">{{$bill->person_name}}</span>
        </div>
        <div style="margin-bottom: 5mm;">
            <span>{{__('Date')}}: </span>
            <span style="font-weight: bold">{{$bill->created_at->format('Y-m-d')}}</span>
        </div>
        <div style="margin-bottom: 5mm">
            <span>{{__('Total Price')}}: </span>
            <span style="font-weight: bold">{{number_format($bill->total_price,$bill->currency==='usd' ? 2 : 0)}}</span>
            <span style="font-weight: bold">{{__($bill->currency)}}</span>
        </div>
        <div style="margin-bottom: 5mm">
            <span style="">{{__('Status')}}:</span>
            <span
                    style="font-size: 28px; font-weight: bold;color: {{$bill->payment_status_color}}">{{__($bill->payment_status)}}</span>
        </div>

        @if($bill->payment_status==="Partially Payed")
            <div style="margin-bottom: 5mm">
                <span>{{__('Payed Price')}}: </span>
                <span style="font-weight: bold">{{number_format($bill->payed_price,$bill->currency==='usd' ? 2 : 0)}}</span>
                <span style="font-weight: bold">{{__($bill->currency)}}</span>
            </div>
            <div style="margin-bottom: 5mm">
                <span>{{__('Remaining Price')}}: </span>
                <span style="font-weight: bold">{{number_format($bill->remaining_price,$bill->currency==='usd' ? 2 : 0)}}</span>
                <span style="font-weight: bold">{{__($bill->currency)}}</span>
            </div>
        @endif

    </div>
@endsection
