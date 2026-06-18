<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BJ Laptop Hub') }}</title>
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}?v={{ filemtime(public_path('favicon.png')) }}">
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v={{ filemtime(public_path('favicon.png')) }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-N5vRtFetEfE9OdLDKnsXHpswJ4cyGmX5a8kTzadxi+u0i7GoCmBbJZzZH+OiJhCE" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
    </body>
</html>
