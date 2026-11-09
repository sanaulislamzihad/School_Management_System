@extends('layouts.dashboard')
@section('title', 'Notices')
@section('content')
<x-page-head title="Notice board" subtitle="Latest school announcements." />
<div class="card divide-y divide-line overflow-hidden">
    @foreach ($notices as $n)
        <a href="{{ route('portal.notices.show', $n->id) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-2">
            <span class="tone-indigo inline-flex size-10 shrink-0 items-center justify-center rounded-xl"><x-lucide-megaphone class="size-5" /></span>
            <span class="min-w-0 flex-1"><span class="block font-bold">{{ $n->title }}</span><span class="block text-xs text-ink-3">{{ nice_date($n->date) }} · {{ $n->category }}</span></span>
            @if ($n->pinned)<span class="badge badge-blue">Pinned</span>@endif
        </a>
    @endforeach
</div>
<div class="mt-4">{{ $notices->links() }}</div>
@endsection
