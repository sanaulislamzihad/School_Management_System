@extends('layouts.dashboard')
@section('title', 'Assignments')
@section('content')
<x-page-head title="Assignments" subtitle="Work given by your teachers." />
@if ($items->isEmpty())
    <div class="card"><x-empty title="No assignments yet" icon="book-open" /></div>
@else
    <div class="space-y-3">
        @foreach ($items as $h)
            @php($open = $h->due_date >= today()->toDateString())
            <article class="card p-5"><div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-bold">{{ $h->title }}</h2><span class="badge {{ $open ? 'badge-amber' : 'badge-gray' }}">{{ $open ? 'Due '.nice_date($h->due_date) : 'Closed' }}</span></div><p class="mt-1 text-xs text-ink-3">{{ $h->subject_name }} · {{ $h->teacher_name }}</p>@if ($h->body)<p class="mt-2 text-sm leading-relaxed text-ink-2">{{ $h->body }}</p>@endif</article>
        @endforeach
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endif
@endsection
