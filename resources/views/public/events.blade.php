@extends('layouts.public')
@section('title', 'Events')
@section('content')
<x-web-page title="School Events">
    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($events as $event)
            <article class="rounded-xl border border-slate-200 p-5"><p class="text-sm font-bold text-leaf-dark">{{ nice_date($event->date) }}</p><h2 class="mt-1 text-lg font-bold text-navy">{{ $event->title }}</h2><p class="text-sm text-slate-600">{{ $event->location }}</p><p class="mt-2 text-sm leading-relaxed">{{ $event->body }}</p></article>
        @empty
            <p class="text-slate-600">New events will be announced soon.</p>
        @endforelse
    </div>
</x-web-page>
@endsection
