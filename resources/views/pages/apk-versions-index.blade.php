<!doctype html>
<html lang="en">
<head>
    <title>Apk Versions Index</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
</head>
@php
    $formInputClass="border-gray-500 focus:border-blue-700 border rounded shadow w-72";
    $cellClass="p-1 border-gray-500 border";
@endphp
<body class="bg-pink-100 p-3">
<div class="bg-yellow-100 rounded-lg border-dashed border border-black">
    <div class="flex justify-end px-2 mt-1">
        <a
            href="{{route('apk-version.logout')}}"
            class="text-right px-4 bg-orange-500 text-white hover:bg-orange-600 cursor-pointer rounded-lg font-bold">
            Logout
        </a>
    </div>
    <h3 class="text-lg font-bold px-2 mb-1">Create Version:</h3>
    @if ($errors->any())
        <div class="p-2 px-3 mb-1 mx-2 rounded-lg bg-red-200 font-bold">
            <div>Errors:</div>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </div>
    @endif
    @if(session('success'))
        <div class="p-2 px-3 mb-1 mx-2 rounded-lg bg-green-200 font-bold text-lg">
            {{session('success')}}
        </div>
    @endif

    <form action="{{route('apk-version.store')}}" method="post" enctype="multipart/form-data"
          class="flex items-start gap-2 flex-wrap p-2 rounded-lg">
        @csrf
        <label for="version">
            <div>Version:</div>
            <input class="{{$formInputClass}}" type="number" name="version"
                   value="{{old('version')}}"
                   placeholder="E.g. 1.00010001" required>
        </label>
        <label for="channel">
            <div>Channel:</div>
            <select name="channel" class="{{$formInputClass}}" required>
                <option value="stable" {{old('channel','stable') === 'stable' ? 'selected' : ''}}>Stable</option>
                <option value="beta" {{old('channel') === 'beta' ? 'selected' : ''}}>Beta</option>
            </select>
        </label>
        <label for="platform">
            <div>Platform:</div>
            <select name="platform" class="{{$formInputClass}}" required>
                <option value="android" {{old('platform','android') === 'android' ? 'selected' : ''}}>Android</option>
                <option value="windows" {{old('platform') === 'windows' ? 'selected' : ''}}>Windows</option>
                <option value="ios" {{old('platform') === 'ios' ? 'selected' : ''}}>IOS</option>
            </select>
        </label>
        <label for="notes">
            <div>Notes:</div>
            <textarea name="notes" class="{{$formInputClass}}">{{old('notes')}}</textarea>
        </label>
        <label for="file">
            <div>File:</div>
            <input type="file" name="file" accept=".apk" class="{{$formInputClass}}" required>
        </label>
        <button type="submit"
                class="border-blue-700 text-white bg-blue-500 hover:bg-blue-600 p-2 w-24 h-10 self-center rounded-lg">
            <i class="fas fa-save"></i> Submit
        </button>
    </form>
    <div class="text-sm text-gray-500 italic p-2">Existing version number will be replaced.</div>
</div>
<div class="py-3">
    <table class="w-full rounded-lg" style="border-collapse: separate; border-spacing: 0">
        <thead>
        <tr>
            <td colspan="10" class="text-center">
                Showing {{$apkVersions->firstItem() ?: 0}} to {{$apkVersions->lastItem() ?: 0}}
                from {{$apkVersions->total()}}
            </td>
        </tr>
        <tr class="bg-blue-300">
            <th class="{{$cellClass}} rounded-tl-lg">#</th>
            <th class="{{$cellClass}}">Version (-)</th>
            <th class="{{$cellClass}}">Channel</th>
            <th class="{{$cellClass}}">Platform</th>
            <th class="{{$cellClass}}">Notes</th>
            <th class="{{$cellClass}}">File Name</th>
            <th class="{{$cellClass}}">File Size</th>
            <th class="{{$cellClass}}">Created At</th>
            <th class="{{$cellClass}}">Updated At</th>
            <th class="{{$cellClass}} rounded-tr-lg">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($apkVersions->items() as $apkVersion)
            <tr>
                <td class="{{$cellClass}}">{{$loop->index+$apkVersions->firstItem()}}</td>
                <td class="{{$cellClass}}">{{$apkVersion->version}}</td>
                <td class="{{$cellClass}}">{{$apkVersion->channel}}</td>
                <td class="{{$cellClass}}">{{$apkVersion->platform}}</td>
                <td class="{{$cellClass}}">{{$apkVersion->notes}}</td>
                <td class="{{$cellClass}}">
                    {{$apkVersion->file_name}}
                    <a href="{{route('apk-version.download',['apk_version'=>$apkVersion->uuid])}}"
                       class="text-blue-500 hover:text-blue-700 cursor-pointer"><i class="fas fa-download"></i></a>
                </td>
                <td class="{{$cellClass}}">{{number_format(\Illuminate\Support\Facades\Storage::fileSize($apkVersion->file_path)/1000)}}
                    KB
                </td>
                <td class="{{$cellClass}}">{{$apkVersion->created_at}}</td>
                <td class="{{$cellClass}}">{{$apkVersion->updated_at}}</td>
                <td class="{{$cellClass}}">
                    <a data-href="{{route('apk-version.destroy',['apk_version'=>$apkVersion->uuid])}}"
                       class="text-red-500 hover:text-red-700 cursor-pointer"
                       onclick="confirmRemove(this)"
                       title="Delete Version"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="flex justify-center gap-4 pt-2">
        @foreach($apkVersions->getUrlRange(1,$apkVersions->lastPage()) as $pageNumber=>$pageUrl)
            @php
                $elActivityClass="text-blue-500 hover:text-blue-700 bg-yellow-100/25";
                if(request()->integer('page',1)===$pageNumber){
                    $elActivityClass="text-black cursor-default bg-blue-300/50";
                }
            @endphp
            <a href="{{$pageUrl}}"
               class="font-bold inline-flex items-center justify-center rounded-lg h-6 w-6 {{$elActivityClass}}">{{$pageNumber}}</a>
        @endforeach
    </div>
</div>

<script>
    function confirmRemove(evt) {
        if (confirm('Sure to delete?')) {
            window.location.href = evt.getAttribute('data-href')
        }
    }
</script>
</body>
</html>
