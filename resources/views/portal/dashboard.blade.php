@extends('layouts.dashboard')
@section('title', 'Dashboard')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
@section('content')
<section class="mb-5 flex flex-wrap items-center justify-between gap-5 rounded-2xl p-6 text-white" style="background: linear-gradient(115deg, #4338ca 0%, #6d4bea 55%, #0f9d94 100%)">
    <div class="flex items-center gap-4">
        <x-avatar :name="$me->name" :photo="$me->photo" class="size-16 border-2 border-white/40 text-lg" />
        <div><p class="text-sm text-indigo-100">{{ $greeting }}</p><h1 class="text-2xl font-extrabold tracking-tight">{{ $me->name }}</h1><p class="text-indigo-100">{{ $me->schoolClass->name }} {{ $me->section->name ?? '' }} · Roll {{ $me->roll }} · ID {{ $me->student_no }}</p></div>
    </div>
    <a href="{{ route('portal.id-card') }}" class="btn border-transparent bg-white text-indigo-800 hover:bg-indigo-50"><x-lucide-id-card class="size-4" /> My ID card</a>
</section>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Overview">
    <x-stat label="Attendance" :value="$attendanceRate !== null ? $attendanceRate.'%' : 'No data'" sub="This session" icon="calendar-check" tone="teal" :href="route('portal.attendance')" />
    <x-stat label="Latest GPA" :value="$latest ? number_format($latest['gpa'], 2) : 'No result'" :sub="$latestExam->name ?? ''" icon="award" tone="violet" :href="route('portal.results')" />
    <x-stat label="Open assignments" :value="$pendingWork" sub="Not past due date" icon="book-open" tone="amber" :href="route('portal.assignments')" />
    <x-stat label="Fees due" :value="money($due)" :sub="$due > 0 ? 'Please clear your dues' : 'All clear'" icon="wallet" :tone="$due > 0 ? 'rose' : 'emerald'" :href="route('portal.accounts')" />
</section>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Today's routine · {{ $todayName }}</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('portal.routine') }}">Full week</a></div>
        @if ($routine->isEmpty())
            <x-empty title="No classes today" icon="calendar-days" />
        @else
            <ul class="divide-y divide-line">@foreach ($routine as $p)<li class="flex items-center gap-3 px-5 py-3"><x-avatar :name="$p->teacher_name ?? 'Teacher'" :photo="$p->teacher_photo" class="size-9" /><span class="min-w-0 flex-1"><span class="block text-sm font-bold">{{ $p->subject_name }}</span><span class="block text-xs text-ink-2">{{ $p->teacher_name }} · Room {{ $p->room }}</span></span><span class="whitespace-nowrap text-xs font-semibold text-ink-2">{{ nice_time($p->start_time) }}</span></li>@endforeach</ul>
        @endif
    </section>
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Assignments</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('portal.assignments') }}">View all</a></div>
        @if ($homework->isEmpty())
            <x-empty title="You are all caught up" icon="circle-check" />
        @else
            <ul class="divide-y divide-line">@foreach ($homework as $h)<li class="flex items-center justify-between gap-3 px-5 py-3"><span class="min-w-0"><span class="block text-sm font-bold">{{ $h->title }}</span><span class="block text-xs text-ink-2">{{ $h->subject_name }}</span></span><span class="badge badge-amber">Due {{ \Carbon\Carbon::parse($h->due_date)->format('M j') }}</span></li>@endforeach</ul>
        @endif
    </section>
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Daily diary</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('portal.diary') }}">View all</a></div>
        @if ($diary->isEmpty())
            <x-empty title="No diary notes yet" icon="notebook-pen" />
        @else
            <ul class="divide-y divide-line">@foreach ($diary as $d)<li class="px-5 py-3"><span class="block text-sm font-bold">{{ $d->title }}</span><span class="block text-xs text-ink-2">{{ nice_date($d->date) }} · {{ $d->body }}</span></li>@endforeach</ul>
        @endif
    </section>
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Recent notices</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('portal.notices') }}">View all</a></div>
        <ul class="divide-y divide-line">@foreach ($notices as $n)<li><a href="{{ route('portal.notices.show', $n->id) }}" class="block px-5 py-3 hover:bg-surface-2"><span class="block text-sm font-bold">{{ $n->title }}</span><span class="block text-xs text-ink-2">{{ nice_date($n->date) }} · {{ $n->category }}</span></a></li>@endforeach</ul>
    </section>
</div>
@endsection
