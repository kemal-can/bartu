@extends('layouts.auth')
@section('title', __('passwords.reset_password'))

@section('content')

    <auth-password-reset email="{{ $email ?? null }}" token="{{ $token }}"></auth-password-reset>

@endsection
