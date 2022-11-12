@extends('layouts.skin')

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('title', $title)

@section('content')
    @if (Auth::check() && !$form->isActive())
        <i-alert variant="warning" :rounded="false">
            {{ __('form.inactive_info') }}
        </i-alert>
    @endif

    <div class="sm:px-5">
        <web-form-view :sections="{{ Js::from($form->sections) }}" :styles="{{ Js::from($form->styles) }}"
            :submit-data="{{ Js::from($form->submit_data) }}" public-url="{{ $form->publicUrl }}"></web-form-view>
    </div>
@endsection
