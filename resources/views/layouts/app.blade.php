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
    {{-- Page Title --}}
    <title>{{ config('app.name') }}</title>
    {{-- Font family --}}
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <!-- Application Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Configuration -->
    <script>
        updateTheme();

        var config = {!! Js::from(array_merge($config, ['csrfToken' => csrf_token()])) !!};
        var lang = {!! Js::from($lang) !!};
    </script>

    <!-- Add all of the custom registered styles -->
    @foreach (\App\Innoclapps\Facades\Innoclapps::styles() as $name => $path)
        @if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']))
            <link rel="stylesheet" href="{!! $path !!}">
        @else
            <link rel="stylesheet" href="{{ url("styles/$name") }}">
        @endif
    @endforeach

    {{-- Head Flag --}}
</head>

<body>
    <div class="h-screen flex overflow-hidden bg-neutral-100 dark:bg-neutral-800" id="app" v-cloak>
        {{-- Sidebar --}}
        <sidebar></sidebar>

        {{-- Main column --}}
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            {{-- Application Warnings --}}
            @include('warnings.dashboard')
            {{-- Application navbar --}}
            <navbar></navbar>
            {{-- Alerts --}}
            @include('alerts')
            {{-- Application Call Handler --}}
            @if (auth()->user()->can('use voip') && config('innoclapps.voip.client') !== null)
                <call-component></call-component>
            @endif
            {{-- Main layout view --}}
            <router-view></router-view>
            {{-- Toaster notifications --}}
            @include('notifications')
            {{-- Confirmation Dialog Component --}}
            <i-confirmation-dialog v-if="dialog && !dialog.injectedInDialog" :dialog="dialog">
            </i-confirmation-dialog>
        </div>
    </div>
    <!-- Application Scripts -->
    <script src="{{ asset('assets/tinymce/tinymce.min.js?v=' . \App\Innoclapps\Application::VERSION) }}"></script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- Initialize Application -->
    <script>
        window.Innoclapps = new CreateApplication(config)
    </script>

    <!-- Add all of the custom registered scripts -->
    @foreach (\App\Innoclapps\Facades\Innoclapps::scripts() as $name => $path)
        @if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']))
            <script src="{!! $path !!}"></script>
        @else
            <script src="{{ url("scripts/$name") }}"></script>
        @endif
    @endforeach

    <script>
        // Start the application
        Innoclapps.start();
    </script>
</body>

</html>
