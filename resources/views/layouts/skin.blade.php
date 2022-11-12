<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

    @if (config('app.favicon_enabled'))
        @include('favicon')
    @endif

    {{-- Theme updater --}}
    @include('theme-change')
    <!-- Page Title -->
    <title>@yield('title')</title>
    {{-- Font family --}}
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <!-- Application Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <script>
        updateTheme();
        var config = {!! Js::from(array_merge($config, ['csrfToken' => csrf_token()])) !!};
        var lang = {!! Js::from($lang) !!};
    </script>

    @stack('head')
</head>

<body>
    <div id="app" v-cloak>
        @yield('content')
        @include('notifications')
    </div>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    <script>
        window.Innoclapps = new CreateApplication(config)

        Innoclapps.start();
    </script>
</body>

</html>
