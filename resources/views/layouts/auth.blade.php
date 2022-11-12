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
        var config = {!! Js::from($config) !!};
        var lang = {!! Js::from($lang) !!};
    </script>

    {{-- Head Flag --}}
</head>

<body>
    <div id="app">
        <div
            class="
        min-h-screen
        bg-neutral-50
        dark:bg-neutral-900
        flex flex-col
        justify-center
        py-12
        sm:px-6
        lg:px-8
        ">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                @include('brand')

                <h2 class="mt-6 text-center text-3xl font-extrabold text-neutral-900 dark:text-white">
                    @yield('title')
                </h2>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-2 sm:px-0">
                <div class="bg-white dark:bg-neutral-800 py-8 px-6 sm:px-10 shadow rounded-lg">
                    <div class="space-y-4">
                        @include('warnings.www-url-prefix')
                        @include('warnings.incorrect-url')
                        {{-- Fake div for spacing when only 1 alert exists --}}
                        <div></div>
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
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
