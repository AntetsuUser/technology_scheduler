<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">
    <!-- iOS用 -->
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- Android用 -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta http-equiv="content-language" content="ja">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <title>{{ config('app.name', 'スケジューラー') }}</title> --}}
    <title>@yield('title', 'スケジューラー')</title>
    {{-- <title>スケジューラー</title> --}}

    <!-- Scripts -->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!--  -->

    <!-- Styles -->
    {{-- @if(config('app.env') === 'production')
        <link href="{{ secure_asset('/css/reset.css') }}" rel="stylesheet">
        <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
    @else
        <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
    @endif --}}

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet"  href="{{ asset('css/bootstrap.min.css') }}">
    
    <link href="{{ asset('css/nav.css') }}" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>

    <!-- 拡大できないようにする -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    @yield('css')
    <style>
        @media screen and (max-width: 1920px) 
        {
            body 
            {
                touch-action: pan-x pan-y;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light nav_back shadow-sm">
            <div class="container d-flex justify-content-start">
                <a class="navbar-brand d-table" href="{{ url('/') }}">
                    生産技術スケジューラー
                </a>
            </div>
        </nav>
        <main class=""> <!-- py-2 -->
            @yield('content')
        </main>
    </div>
</body>
</html>