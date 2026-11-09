@extends('layouts.dashboard')
@section('title', 'Notices')
@section('content')
<x-page-head title="Notices" subtitle="School announcements and staff notes." />
<div class="space-y-3">
    @foreach ($notices as $n)
        <article class="card p-5"><div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-bold">{{ $n->title }}</h2><span class="badge {{ status_badge($n->audience) }}">{{ $n->audience }}</span></div><p class="mt-1 text-xs text-ink-3">{{ nice_date($n->date) }} · {{ $n->category }}</p><p class="mt-2 text-sm leading-relaxed text-ink-2">{{ $n->body }}</p></article>
    @endforeach
</div>
<div class="mt-4">{{ $notices->links() }}</div>
@endsection
