@extends('layouts.report')
@section('report-content')
    <style>
        tbody tr:nth-child(even) {
            background: #f1ecec;
        }
    </style>

    @foreach($groupedStats as $groupName => $stats)
        <table style="margin: 10px 0">
            <thead>
            <tr>
                @php
                    $groupHeaderColor= match($groupName){
                      "confirmed"=>"#a3ec34",
                      "un_confirmed"=>"#e797b1",
                      default=>"#82c3de"
                    };
                @endphp
                <th colspan="3" style="background: {{$groupHeaderColor}}">
                    {{__($groupName.' Lists')}}
                </th>
            </tr>
            <tr>
                <th>#</th>
                <th>{{__('Parameter')}}</th>
                <th>{{__('Result')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $parameterKey=>$parameterValue)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{__(\Illuminate\Support\Str::remove(['_usd','_iqd'],$parameterKey))}}</td>
                    <td style="font-weight: bold;">{!! $parameterValue!!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    @endforeach
@endsection
