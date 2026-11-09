@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('content')
<section class="mb-5 flex flex-wrap items-center justify-between gap-5 rounded-2xl p-6 text-white" style="background: #4338ca">
    <div class="flex items-center gap-4">
        <x-avatar :name="$me->name" :photo="$me->photo" class="size-16 border-2 border-white/40 text-lg" />
        <div><p class="text-sm text-indigo-100">{{ now()->format('l, F j, Y') }}</p><h1 class="text-2xl font-extrabold tracking-tight">Welcome, {{ $me->name }}</h1><p class="text-indigo-100">{{ $me->designation }}</p></div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('teacher.attendance.index') }}" class="btn border-transparent bg-white text-indigo-800 hover:bg-indigo-50"><x-lucide-calendar-check class="size-4" /> Take attendance</a>
        <a href="{{ route('teacher.marks.index') }}" class="btn border-white/30 bg-white/10 text-white hover:bg-white/20"><x-lucide-file-pen-line class="size-4" /> Enter marks</a>
    </div>
</section>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Overview">
    <x-stat label="Today's classes" :value="$today->count()" :sub="now()->format('l')" icon="calendar-days" tone="indigo" :href="route('teacher.timetable')" />
    <x-stat label="My sections" :value="$sectionCount" :sub="$links->count().' subjects'" icon="school" tone="teal" />
    <x-stat label="My students" :value="$studentCount" sub="In assigned sections" icon="graduation-cap" tone="sky" :href="route('teacher.students')" />
    <x-stat label="Open assignments" :value="$homeworkDue" sub="Not past due date" icon="book-open" tone="amber" :href="route('teacher.resource.index', 'homework')" />
</section>

@if ($unmarked->isNotEmpty())
    <div class="mt-5 flex flex-wrap items-center gap-2 rounded-xl bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-200">
        <x-lucide-triangle-alert class="size-5 shrink-0" />
        <span class="font-semibold">Attendance not marked today:</span>
        @foreach ($unmarked as $l)
            <a class="badge badge-amber hover:underline" href="{{ route('teacher.attendance.index', ['class_id' => $l->class_id, 'section_id' => $l->section_id]) }}">{{ $l->class_name }} {{ $l->section_name }}</a>
        @endforeach
    </div>
@endif

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Today's schedule</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('teacher.timetable') }}">Full week</a></div>
        @if ($today->isEmpty())
            <x-empty title="No classes today" icon="calendar-days" />
        @else
            <ul class="divide-y divide-line">@foreach ($today as $p)<li class="flex items-center justify-between gap-3 px-5 py-3"><span><span class="block text-sm font-bold">{{ $p->subject_name }}</span><span class="block text-xs text-ink-2">{{ $p->class_name }} {{ $p->section_name }} · Room {{ $p->room }}</span></span><span class="whitespace-nowrap text-xs font-semibold text-ink-2">{{ nice_time($p->start_time) }} to {{ nice_time($p->end_time) }}</span></li>@endforeach</ul>
        @endif
    </section>
    <section class="card overflow-hidden">
        <h2 class="card-head card-title">My subjects</h2>
        @if ($links->isEmpty())
            <x-empty title="No subjects assigned yet" text="Please ask the administrator to assign your classes." icon="book-open" />
        @else
            <ul class="max-h-80 divide-y divide-line overflow-y-auto">@foreach ($links as $l)<li class="flex items-center justify-between gap-3 px-5 py-3"><span><span class="block text-sm font-bold">{{ $l->subject_name }}</span><span class="block text-xs text-ink-2">{{ $l->class_name }} {{ $l->section_name }}</span></span>@if ($l->is_class_teacher)<span class="badge badge-blue">Class teacher</span>@endif</li>@endforeach</ul>
        @endif
    </section>
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Recent assignments</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('teacher.resource.index', 'homework') }}">Manage</a></div>
        @if ($homework->isEmpty())
            <x-empty title="No assignments yet" icon="book-open" />
        @else
            <ul class="divide-y divide-line">@foreach ($homework as $h)<li class="px-5 py-3"><span class="block text-sm font-bold">{{ $h->title }}</span><span class="block text-xs text-ink-2">{{ $h->class_name }} {{ $h->section_name }} · Due {{ nice_date($h->due_date) }}</span></li>@endforeach</ul>
        @endif
    </section>
    <section class="card overflow-hidden">
        <div class="card-head"><h2 class="card-title">Staff notices</h2><a class="text-sm font-semibold text-primary-ink" href="{{ route('teacher.notices') }}">All notices</a></div>
        <ul class="divide-y divide-line">@foreach ($notices as $n)<li class="px-5 py-3"><span class="block text-sm font-bold">{{ $n->title }}</span><span class="block text-xs text-ink-2">{{ nice_date($n->date) }}</span></li>@endforeach</ul>
    </section>
</div>
@endsection
