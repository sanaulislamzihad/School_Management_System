@extends('layouts.dashboard')
@section('title', 'Results')
@section('content')
<x-page-head title="Results" subtitle="Pick an exam and class to see GPA and the merit list.">
    <a class="btn" href="{{ route('admin.resource.index', 'grades') }}">Grading scale</a>
    @if ($exam)
        <form method="POST" action="{{ route('admin.results.publish', $exam->id) }}">@csrf<button class="btn btn-primary">{{ $exam->is_published ? 'Hide from students' : 'Publish result' }}</button></form>
    @endif
</x-page-head>

<form method="GET" class="card mb-4 grid items-end gap-3 p-4 sm:grid-cols-4">
    <div><label class="field-label" for="exam_id">Exam</label><select id="exam_id" name="exam_id" class="input" required><option value="">Select exam</option>@foreach ($exams as $o)<option value="{{ $o['value'] }}" @selected(request('exam_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach</select></div>
    <x-class-picker :classes="$classes" :sections="$sections" :section-required="false" />
    <button class="btn btn-primary">Show results</button>
</form>

@if ($rows)
    @php($passed = collect($rows)->where('passed', true)->count())
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-stat label="Students" :value="count($rows)" icon="graduation-cap" tone="indigo" />
        <x-stat label="Pass rate" :value="round($passed / count($rows) * 100).'%'" :sub="$passed.' passed'" icon="award" tone="emerald" />
        <x-stat label="Average GPA" :value="number_format(collect($rows)->avg('gpa'), 2)" icon="chart-line" tone="violet" />
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Position</th><th scope="col">Student</th><th scope="col">Section</th><th scope="col">Total</th><th scope="col">GPA</th><th scope="col">Grade</th><th scope="col">Result</th></tr></thead>
                <tbody>@foreach ($rows as $r)<tr><td class="font-bold text-ink">{{ $r['rank'] }}</td><td><span class="block font-semibold text-ink">{{ $r['student']->name }}</span><span class="block text-xs text-ink-3">{{ $r['student']->student_no }}</span></td><td>{{ $r['student']->section_name }}</td><td>{{ $r['total'] + 0 }} / {{ $r['full'] + 0 }}</td><td>{{ number_format($r['gpa'], 2) }}</td><td>{{ $r['letter'] }}</td><td><span class="badge {{ $r['passed'] ? 'badge-green' : 'badge-red' }}">{{ $r['passed'] ? 'Passed' : 'Failed' }}</span></td></tr>@endforeach</tbody>
            </table>
        </div>
    </div>
@elseif (request('class_id'))
    <div class="card"><x-empty title="No marks have been entered yet" icon="file-pen-line" /></div>
@endif
@endsection
