@extends('layouts.auth')
@section('title', __('auth.login'))

@section('content')

    {{-- Login Form Start Flag --}}
    <auth-login></auth-login>
    {{-- Login Form End Flag --}}


@endsection
