@extends('layouts.public')
@section('title', 'Video Gallery')
@section('content')
<x-web-page title="Video Gallery">
    <div class="grid gap-5 sm:grid-cols-2">@foreach ($videos as $video)<x-video-card :video="$video" />@endforeach</div>
</x-web-page>
@endsection
