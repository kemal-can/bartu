@extends('layouts.skin')

@section('title', 'Database Update Required')

@section('content')
    <div class="h-screen min-h-screen bg-neutral-100 dark:bg-neutral-800">
        <migrate-database></migrate-database>
    </div>
@endsection
