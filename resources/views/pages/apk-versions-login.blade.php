<!doctype html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
</head>
@php
    $formInputClass="border-gray-500 focus:border-blue-700 border rounded shadow w-48";
@endphp
<body class="bg-pink-100 p-3 flex items-center justify-center h-[100vh]">
<div class="bg-yellow-100 rounded-lg p-4 w-96">
    @if ($errors->any())
        <div class="p-2 px-3 mb-1 mx-2 rounded-lg bg-red-200 font-bold">
            <div>Errors:</div>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </div>
    @endif
    <form action="{{route('apk-version.login')}}" method="post"
          class="p-2 rounded-lg">
        @csrf
        <label for="key" class="w-full">
            <div class="font-bold mb-2">
                Login:
                <i class="fas fa-key"></i>
            </div>
            <input class="border-gray-500 focus:border-blue-700 border rounded shadow w-full h-10 px-2" type="text"
                   name="key"
                   value="" required autofocus>
        </label>
        <button type="submit"
                class="border-blue-700 mt-2 text-white bg-blue-500 hover:bg-blue-600 p-2 w-full h-10 self-center rounded-lg">
            Submit <i class="fas fa-sign-in"></i>
        </button>
    </form>
</div>
</body>
</html>
