@extends('layouts.app-pdf')
@section('title')
    {{__($reportTitle ?? 'Statistics')}}
@endsection
@section('content')

    <div style="position: relative ;font-weight: normal;  direction: rtl; text-align: right" dir="rtl">
        <div
            style="text-align: center; font-weight: bold; font-size: 2rem;text-decoration: underline;color: purple">
            {{__($reportTitle ??  'Statistics')}}
        </div>
    </div>
    @if($startDate)
        <table class="empty-table" style="margin-bottom: 2mm;width: 100%">
            <thead>
            <tr>
                <td>
                    <span style="padding: 0 3mm">{{__('From Date')}}</span>
                    <span
                        style="padding: 0 3mm;font-weight: bold; direction: ltr;display: inline-block">{{$startDate->format('Y-m-d')}}</span>
                    <span style="padding: 0 3mm">{{__('To')}}</span>
                    <span
                        style="padding: 0 3mm;font-weight: bold; direction: ltr;display: inline-block">{{$endDate->format('Y-m-d')}}</span>
                </td>
                <td style="text-align: left; direction: ltr;vertical-align: center">
                    <span style="font-size: 0.6rem">Printed At: {{now()->format('Y-m-d h:i A')}}</span>
                </td>
            </tr>
            </thead>
        </table>
    @else
        <div style="font-size: 0.6rem;direction: ltr; padding: 0 10px">Printed
            At: {{now()->format('Y-m-d h:i A')}}</div>
    @endif

    @if($description ?? '')
        {!! $description !!}
    @endif

    @yield('report-content')
@endsection
