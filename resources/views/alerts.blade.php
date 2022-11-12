@if ($message = Session::get('success'))
    <i-alert variant="success" dismissible>
        {{ $message }}
    </i-alert>
@endif

@if ($message = Session::get('error'))
    <i-alert variant="danger" dismissible>
        {{ $message }}
    </i-alert>
@endif


@if ($message = Session::get('warning'))
    <i-alert variant="warning" dismissible>
        {{ $message }}
    </i-alert>
@endif


@if ($message = Session::get('info'))
    <i-alert variant="info" dismissible>
        {{ $message }}
    </i-alert>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <i-alert variant="danger" dismissible>
            {{ $error }}
        </i-alert>
    @endforeach
@endif

{{-- Alerts end flag --}}
