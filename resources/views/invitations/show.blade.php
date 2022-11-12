@extends('layouts.skin')

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('title', __('user.accept_invitation'))

@section('content')
    <div class="h-screen min-h-screen bg-neutral-100 dark:bg-neutral-800">
        <div class="pt-20">
            <div class="max-w-2xl mx-auto">
                @include('brand')
                <div class="px-2 sm:px-0">
                    <i-card class="mt-6">
                        <invitation-accept-form :invitation="{{ Js::from($invitation) }}"
                            date-format="{{ config('innoclapps.date_format') }}"
                            time-format="{{ config('innoclapps.time_format') }}"
                            first-day-of-week="{{ settings('first_day_of_week') }}" />
                    </i-card>
                </div>
            </div>
        </div>
    </div>
@endsection
