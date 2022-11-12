@extends('layouts.skin')

@section('title', (settings('company_name') ?: config('app.name')) . ' - ' . __('app.privacy_policy'))

@section('content')
    <div class="h-screen min-h-screen bg-neutral-50 dark:bg-neutral-800">
        <div class="w-full border-b border-neutral-200 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-900">
            <div class="max-w-6xl m-auto">
                <div class="p-4">
                    <h5 class="text-lg text-neutral-800 dark:text-neutral-200 font-semibold">
                        {{ (settings('company_name') ?: config('app.name')) . ' - ' . __('app.privacy_policy') }}
                    </h5>
                </div>
            </div>
        </div>
        <div class="max-w-6xl m-auto">
            <div class="p-4 wysiwyg-text">
                {!! $content !!}
            </div>
        </div>
    </div>
@endsection
