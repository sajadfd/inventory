@php
    use App\Enums\SaleType;
    $type=get_class($list);
    $isPurchaseList=$type===\App\Models\PurchaseList::class;
    $isSaleList=$type===\App\Models\SaleList::class;
$isInventoryList=$isSaleList && $list->type===SaleType::InventorySale;
@endphp

@extends('layouts.app-pdf')
@section('title')
    {{__($reportTitle ?? 'List')}}
@endsection
@section('content')
    <style>
        table.info-table td {
            /*width: 17%;*/
        }

        .cellTitle {
            background: #e0e5e4;
        }

        .cellValue {
            background: #cfd9d7;
            font-weight: bold;
        }
    </style>
    <table class="info-table" style="font-size: 16px;width: 100%;table-layout: fixed">
        <tr>
            <td class="cellTitle">نوع القائمة</td>
            <td class="cellValue">{{$isPurchaseList ? __('Purchase') : ($isInventoryList ? __('Inventory Sale') : __('Store Sale'))}}</td>
            <td class="cellTitle">التاريخ</td>
            <td class="cellValue" dir="ltr" style="text-align: left;direction: ltr">
                <div style="font-weight: bold">{{$list->date->format('Y-m-d')}}</div>
                <div style="font-size: 0.9rem">{{$list->date->format('h:m A')}}</div>
            </td>
            <td class="cellTitle">رمز الفاتورة</td>
            <td class="cellValue" style="text-align: center">
                @if(!empty($qrCodeSvg))
                    <img src="data:image/svg+xml;base64,{{base64_encode($qrCodeSvg)}}"
                         alt="{{$list->bill->code}}"
                         style="width: 60px; height: 60px;">
                @elseif($list->is_confirmed===false)
                    <b>القائمة غير مؤكدة</b>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="6" style="height: 10px"></td>
        </tr>
        <tr>
            <td class="cellTitle">
                {{$isPurchaseList ? __('Supplier') : __('Customer') }}
            </td>
            <td class="cellValue">{{$list->person->name}}</td>
            <td class="cellTitle">هاتف</td>
            <td class="cellValue">{{$list->person->phone}}</td>
            <td class="cellTitle">العنوان</td>
            <td class="cellValue">{{$list->person->address}}</td>
        </tr>
    </table>

    @if($isSaleList && $list->type===SaleType::InventorySale)
        <table style="margin-top: 20px; font-size: 0.7rem">
            <tr>
                <td class="cellTitle">{{__('Car Type')}}</td>
                <td class="cellValue">{{$list->car->car_type_name}}</td>
                <td class="cellTitle">{{__('Car Model')}}</td>
                <td class="cellValue">{{$list->car->car_model_name}}</td>
                <td class="cellTitle">{{__('Manufacture Year')}}</td>
                <td class="cellValue">{{$list->car->model_year}}</td>

                <td class="cellTitle">{{__('VIN')}}</td>
                <td class="cellValue">{{$list->car->vin}}</td>
            </tr>
            <tr>
                <td class="cellTitle">{{__('Color')}}</td>
                <td class="cellValue">{{$list->car->color_name}}</td>
                <td class="cellTitle">{{__('Plate Number')}}</td>
                <td class="cellValue">{{$list->car->plate_number}}</td>
                <td class="cellTitle">{{__('Meter Counter')}}</td>
                <td class="cellValue">{{$list->car->meter_number}}</td>
                <td class="cellTitle">{{__('Diagnosis')}}</td>
                <td class="cellValue">{{$list->diagnosis->name}}</td>
            </tr>
            @if($isInventoryList)
                <tr>
                    <td class="cellTitle" colspan="2">{{__('Mechanic')}}</td>
                    <td class="cellValue" colspan="6">{{$list->mechanic?->name}}</td>
                </tr>
            @endif
        </table>
    @endif
    <table style="margin-top: 10px">
        <thead>
        <tr>
            <th>#</th>
            <th>المادة</th>
            <th>الكمية</th>
            <th>سعر القطعة</th>
            <th>الاجمالي</th>
        </tr>
        </thead>
        <tbody>
        @if($isInventoryList)
            <tr style="background: #e4e8ce">
                <th colspan="5" style="font-size: 1.1rem">
                    {{__('Products')}}
                </th>
            </tr>
        @endif
        @forelse($list->items as $item)
            <tr>
                <td>{{$loop->index+1}}</td>
                <td>{{$item->product->name}}</td>
                <td>{{$item->count}}</td>
                <td>{{number_format($item->price)}}</td>
                <td>{{number_format($item->total_price)}} {{__($item->currency)}}</td>
            </tr>
        @empty
            @if($isSaleList && !$list->is_confirmed && (!isset($emptyRowsIfNoItems) || $emptyRowsIfNoItems===true))
                @foreach(range(1,5) as $i)
                    <tr>
                        <td>{{$i}}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5">{{__('No Items')}}</td>
                </tr>
            @endif
        @endforelse
        @if($isInventoryList)
            @if($list->items->isNotEmpty())
                <tr style="border: 1px solid black; border-top: 3px solid black">
                    <td class="cellTitle" colspan="4" style="text-align: left">{{__('Products Price')}}</td>
                    <td class="cellValue">{{number_format($list->sale_items_total_price ?: $list->total_price)}} {{$list->currency}}</td>
                </tr>
            @endif
            <tr style="background: #e4e8ce;padding-top: 5px">
                <th colspan="5" style="font-size: 1.1rem">
                    {{__('Services')}}
                </th>
            </tr>
            @forelse($list->serviceItems as $item)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{$item->service->name}}</td>
                    <td>{{$item->count}}</td>
                    <td>{{number_format($item->price)}}</td>
                    <td>{{number_format($item->total_price)}} {{__($item->currency)}}</td>
                </tr>
            @empty
                @if($isSaleList && !$list->is_confirmed && (!isset($emptyRowsIfNoItems) || $emptyRowsIfNoItems===true))
                    @foreach(range(1,5) as $i)
                        <tr>
                            <td>{{$i}}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5">{{__('No Items')}}</td>
                    </tr>
                @endif
            @endforelse
            @if($list->serviceItems->isNotEmpty())
                <tr style="border: 1px solid black; border-top: 3px solid black">
                    <td class="cellTitle" colspan="4" style="text-align: left">{{__('Services Price')}}</td>
                    <td class="cellValue">{{number_format($list->service_items_total_price)}} {{__($list->currency)}}</td>
                </tr>
            @endif
        @endif

        </tbody>
    </table>

    @php
        $bill=$list->bill;
    @endphp
    <table style="margin-top: 25px">
        @if($bill)
            <tr>
                <td class="cellTitle">{{__('Total Price')}}</td>
                <td class="cellValue">{{number_format($bill->total_price,$bill->currency==='usd' ? 2 : 0)}} {{__($bill->currency)}}</td>
                <td class="cellTitle">{{__('Payed Price')}}</td>
                <td class="cellValue">{{number_format($bill->payed_price,$bill->currency==='usd' ? 2 : 0)}} {{__($bill->currency)}}</td>
            </tr>
        @endif
        <tr>
            <td class="cellTitle">{{__('List Remaining')}}</td>
            <td class="cellValue">
                @if($bill)
                    {{number_format($bill->remaining_price,$bill->currency==='usd' ? 2 : 0)}} {{__($bill->currency)}}
                @else
                    &nbsp;
                @endif
            </td>
            <td class="cellTitle">{{__('Other Debts')}}</td>
            <td class="cellValue">{{number_format($list->person->debts_in - $bill?->remaining_price)}} {{__($bill?->currency?:$list->currency)}}</td>
        </tr>
    </table>
@endsection
