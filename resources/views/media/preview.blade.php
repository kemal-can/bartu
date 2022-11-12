@extends('layouts.skin')

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('title', __('app.file_preview'))

@section('content')
    <div class="h-screen min-h-screen bg-neutral-50 dark:bg-neutral-800">
        <div class="w-full border-b border-neutral-200 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-900">
            <div class="max-w-6xl m-auto">
                <div class="flex items-center p-4">
                    <div class="grow">
                        <h5 class="text-lg text-neutral-800 dark:text-neutral-200 font-semibold">
                            {{ __('app.file_preview') }}
                        </h5>
                    </div>
                    <div class="space-x-2 flex items-center">
                        <copy-button icon="Share" class="mr-4" text="{{ $media->getViewUrl() }}"
                            success-message="{{ __('media.link_copied') }}">
                        </copy-button>
                        <a href="{{ $media->getDownloadUrl() }}" class="btn btn-primary btn-md rounded-md">
                            {{ __('app.download') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-6xl m-auto">

            <div class="flex flex-col w-full p-4">
                @if ($media->aggregate_type === 'image')
                    <img src="{{ $media->getPreviewUri() }}" class="rounded mx-auto" alt="{{ $media->basename }}">
                @elseif($media->aggregate_type === 'pdf')
                    <div class="aspect-w-16 aspect-h-9">
                        <iframe src="{{ $media->getPreviewUri() }}" name="{{ $media->filename }}" allowfullscreen>
                        </iframe>
                    </div>
                @elseif($media->mime_type === 'text/plain')
                    <div class="text-left whitespace-normal">
                        {{ $media->contents() }}
                    </div>
                @elseif($media->aggregate_type === 'video' && $media->isHtml5SupportedVideo())
                    <div class="aspect-w-16 aspect-h-9">
                        <video autoplay controls>
                            <source src="{{ $media->getPreviewUri() }}" type="{{ $media->mime_type }}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @elseif($media->aggregate_type === 'audio' && $media->isHtml5SupportedAudio())
                    <audio autoplay controls>
                        <source src="{{ $media->getPreviewUri() }}" type="{{ $media->mime_type }}">
                        Your browser does not support the audio tag.
                    </audio>
                @else
                    <p class="text-neutral-600 dark:text-neutral-200 text-center">
                        {{ __('media.no_preview_available') }}
                    </p>
                @endif
                <div class="mt-5 text-center">
                    <a href="{{ $media->getDownloadUrl() }}" class="link">{{ $media->basename }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
