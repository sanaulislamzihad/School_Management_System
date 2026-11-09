@extends('layouts.dashboard')
@section('title', 'Daily diary')
@section('content')
<x-page-head title="Daily diary" subtitle="What your class covered each day." />
@if ($items->isEmpty())
    <div class="card"><x-empty title="No diary notes yet" icon="notebook-pen" /></div>
@else
    <div class="space-y-3">
        @foreach ($items as $d)
            <article class="card p-5"><div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-bold">{{ $d->title }}</h2><span class="text-xs font-semibold text-ink-2">{{ nice_date($d->date) }}</span></div><p class="mt-1 text-xs text-ink-3">{{ $d->subject_name }} · {{ $d->teacher_name }}</p>@if ($d->body)<p class="mt-2 text-sm leading-relaxed text-ink-2">{{ $d->body }}</p>@endif</article>
        @endforeach
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endif
@endsection
