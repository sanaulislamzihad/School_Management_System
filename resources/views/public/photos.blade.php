@extends('layouts.public')
@section('title', 'Photo Gallery')
@section('content')
<x-web-page title="Photo Gallery">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($photos as $photo)
            <figure class="relative h-48 overflow-hidden rounded-xl">
                <img src="{{ asset($photo->image_url) }}" alt="{{ $photo->title }}" loading="lazy" class="size-full object-cover">
                <figcaption class="absolute inset-x-0 bottom-0 bg-black/60 px-2.5 py-1.5 text-[13px] font-medium text-white">{{ $photo->title }}</figcaption>
            </figure>
        @endforeach
    </div>
</x-web-page>
@endsection
