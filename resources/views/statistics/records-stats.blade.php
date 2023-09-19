@extends('layouts.report')
@php
    use Illuminate\Support\Arr;

    $groupHeaderColor = [
        'confirmed' => '#ebf5df',
        'un_confirmed' => '#efe8eb',
        'total' => '#e0eaef',
    ];

    $alternateColors = [
        '#f1ecec',
        '#ffffff',
    ];
    $groupAble = $groupAble ?? false;

@endphp
@section('report-content')
    <table style="font-size: 14px;">
        <thead>
        <tr>
            <th>#</th>
            @foreach($parametersInfo['columns'] as $columnKey=>$column)
                <th>
                    {{$column['title']}}
                    @if($parametersInfo['sort_by']===$columnKey)
                        <span style="font-family: 'dejavusans'">@if($parametersInfo['sort_direction']==='asc')
                                ↑
                            @else
                                ↓️
                            @endif️
                        </span>
                    @endif
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @forelse($recordsFormatted as $groups)
            @php
                $counter =$loop->index+1;
            @endphp

            @if($groupAble)
                {{--Groups--}}
                @php
                    $sameGroupAsTotal=false;
                @endphp
                @php
                    foreach ($groups as $groupName => $groupData) {
                        if ($groupName === 'total') continue;
                        if (isset($groups['total']) && json_encode($groupData) === json_encode($groups['total'])) {
                            unset($groups[$groupName]);
                            $sameGroupAsTotal = __('Total and ' . $groupName . ' Lists');
                        }
                    }
                    $groupedColumnsCount = count(array_filter($parametersInfo['columns'], fn($columnData) => $columnData['group_position'] ?? false));
                @endphp
                @foreach($groups as $groupName=>$groupData)
                    <tr style="page-break-inside: avoid ;background: {{$alternateColors[$counter%2]}}">
                        @if($loop->index === 0)
                            <td rowspan="{{count($groups) *2}}"
                                style="border-bottom: 3px solid black;">{{$counter}}</td>
                            @foreach($parametersInfo['columns'] as $columnName=>$columnData)
                                @if(($columnData['group_position'] ?? false)==='start')
                                    <td rowspan="{{count($groups) *2}}"
                                        style="border-bottom: 3px solid black; font-size: 1.1rem;font-weight: bold; vertical-align: top">{{$groupData[$columnName]}}</td>
                                @endif
                            @endforeach
                        @endif
                        <th colspan="{{count($parametersInfo['columns'])-$groupedColumnsCount}}"
                            style=" text-align: center; background: {{$groupHeaderColor[$groupName]}}">
                            {{__($sameGroupAsTotal ?: $groupName.' Lists')}}
                        </th>
                        @if($loop->index === 0)
                            @foreach($parametersInfo['columns'] as $columnName=>$columnData)
                                @if(($columnData['group_position'] ?? false)==='end')
                                    <td rowspan="{{count($groups) *2}}"
                                        style="border-bottom: 3px solid black; font-size: 1.1rem;font-weight: bold; vertical-align: top">{{$groupData[$columnName]}}</td>
                                @endif
                            @endforeach
                        @endif
                    </tr>
                    <tr style="background: {{$alternateColors[$counter%2]}};border: 1px solid black;background: {{$groupHeaderColor[$groupName]}};
                    @if($loop->last) border-bottom: 3px solid black @endif">
                        @foreach($parametersInfo['columns'] as $columnName=>$columnData)
                            @if($columnData['group_position']??false)
                                @continue
                            @endif
                            <td>{!! $groupData[$columnName] ?? '' !!}</td>
                        @endforeach
                    </tr>

                @endforeach
            @else
                {{--If Not Groups--}}
                @php
                    $record=$groups;
                @endphp
                <tr>
                    <td>{{$counter}}</td>
                    @foreach($parametersInfo['columns'] as $columnName=>$columnData)
                        <td style="{{$columnData['style'] ?? ''}}">
                            <span style="{{Arr::get($record,$columnData['text_style_column'] ?? '','')}}">
                            @if(($columnData['type'] ?? '') ==='date')
                                    {{\Carbon\Carbon::make(Arr::get($record,$columnName,''))->format($columnData['format'] ?? 'Y-m-d H:i A')}}
                                @else
                                    {!! Arr::get($record,$columnName,'') !!}
                                @endif
                            </span>
                        </td>
                    @endforeach
                </tr>
            @endif

        @empty
            <tr>
                <td colspan="{{count($parametersInfo['columns'])}}" style="text-align: center">{{__("No Data")}}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection

