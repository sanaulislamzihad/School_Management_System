@extends('layouts.dashboard')
@section('title', 'Attendance')
@section('content')
<x-page-head title="Student attendance" subtitle="Choose a class and date, then save the register." />

<form method="GET" class="card mb-4 grid items-end gap-3 p-4 sm:grid-cols-4">
    <x-class-picker :classes="$classes" :sections="$sections" />
    <div><label class="field-label" for="date">Date</label><input id="date" type="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}" class="input" required></div>
    <button class="btn btn-primary">Load register</button>
</form>

@if ($students->isNotEmpty())
    <form method="POST" action="{{ staff_route('attendance.store') }}" class="card overflow-hidden">
        @csrf
        <input type="hidden" name="class_id" value="{{ request('class_id') }}">
        <input type="hidden" name="section_id" value="{{ request('section_id') }}">
        <input type="hidden" name="date" value="{{ $date }}">
        <div class="card-head flex-wrap">
            <div>
                <h2 class="card-title">{{ nice_date($date) }} · {{ $students->count() }} students</h2>
                <p class="text-xs text-ink-3">{{ $saved ? 'Already saved. Change anything and save again.' : 'Everyone starts as Present.' }} P Present, A Absent, L Late, V Leave.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" class="btn btn-sm" onclick="this.closest('form').querySelectorAll('input[data-status=Present]').forEach(r => r.checked = true)">Mark all present</button>
                <button class="btn btn-sm btn-primary">Save attendance</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Roll</th><th scope="col">Student</th><th scope="col">ID</th><th scope="col" class="text-right">Status</th></tr></thead>
                <tbody>
                    @foreach ($students as $s)
                        <tr><td>{{ $s->roll }}</td><td class="font-semibold text-ink">{{ $s->name }}</td><td>{{ $s->student_no }}</td><td class="text-right"><x-attendance-row :id="$s->id" :value="$s->attendance" /></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
@elseif (request('section_id'))
    <div class="card"><x-empty title="No students in this section" icon="graduation-cap" /></div>
@else
    <div class="card"><x-empty title="Pick a class to begin" text="The register will appear here." icon="calendar-check" /></div>
@endif
@endsection
