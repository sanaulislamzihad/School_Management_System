@extends('layouts.dashboard')
@section('title', 'Timetable')
@section('content')
<x-page-head title="Weekly timetable" subtitle="Choose a class and section to see its week.">
    <a class="btn btn-primary" href="{{ route('admin.resource.index', 'routines') }}"><x-lucide-pencil class="size-4" /> Edit periods</a>
</x-page-head>
<form method="GET" class="card mb-4 grid items-end gap-3 p-4 sm:grid-cols-3">
    <x-class-picker :classes="$classes" :sections="$sections" />
    <button class="btn btn-primary">Show timetable</button>
</form>
@if (request('section_id'))
    <x-week-grid :periods="$periods" />
@else
    <div class="card"><x-empty title="Pick a class to view its timetable" icon="calendar-days" /></div>
@endif
@endsection
