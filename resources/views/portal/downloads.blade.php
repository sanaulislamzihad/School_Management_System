@extends('layouts.dashboard')
@section('title', 'Downloads')
@section('content')
<x-page-head title="Downloads" subtitle="Syllabus, calendar and other school files." />
<div class="card overflow-hidden">
    @if ($downloads->isEmpty())
        <x-empty title="No files yet" icon="download" />
    @else
        <ul class="divide-y divide-line">
            @foreach ($downloads as $d)
                <li class="flex items-center justify-between gap-3 px-5 py-3.5"><span><span class="block text-sm font-bold">{{ $d->title }}</span><span class="block text-xs text-ink-3">{{ $d->category }} · {{ nice_date($d->date) }}</span></span>@if ($d->url && $d->url !== '#')<a class="btn btn-sm" href="{{ $d->url }}" target="_blank" rel="noopener">Open</a>@else<span class="text-xs text-ink-3">File coming soon</span>@endif</li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
