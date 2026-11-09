@extends('layouts.dashboard')
@section('title', $notice->title)
@section('content')
<x-page-head :title="$notice->title" :subtitle="nice_date($notice->date).' · '.$notice->category">
    <a class="btn" href="{{ route('portal.notices') }}"><x-lucide-arrow-left class="size-4" /> All notices</a>
</x-page-head>
<article class="card max-w-3xl p-6 leading-relaxed text-ink-2">{{ $notice->body }}</article>
@endsection
