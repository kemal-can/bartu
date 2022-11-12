@if (Str::startsWith(config('app.url'), 'https://') && !Request::isSecure())
    <i-alert variant="danger" :rounded="{{ !Auth::check() ? 'true' : 'false' }}">
        You must <a href="{{ config('app.url') }}" class="text-danger-800 hover:text-danger-600 font-semibold">access</a>
        the installation URL with <span class="font-semibold">https</span>.
    </i-alert>
@elseif (Str::startsWith(config('app.url'), 'http://') && Request::isSecure())
    <i-alert variant="danger" :rounded="{{ !Auth::check() ? 'true' : 'false' }}">
        Incorrect application URL, update the <code class="bg-danger-100 px-1 rounded">.env</code> file <span
            class="font-semibold">APP_URL</span> config
        value to start with <span class="font-semibold">https://</span> and delete all the <span
            class="font-semibold">.php</span> files in <span class="font-semibold">bootstrap/cache</span>
    </i-alert>
@endif
