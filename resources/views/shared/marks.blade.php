@extends('layouts.dashboard')
@section('title', 'Marks entry')
@section('content')
<x-page-head title="Marks entry" subtitle="Choose an exam, class and subject. Empty boxes are not saved." />

<form method="GET" class="card mb-4 grid items-end gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
    <div><label class="field-label" for="exam_id">Exam</label><select id="exam_id" name="exam_id" class="input" required><option value="">Select exam</option>@foreach ($exams as $o)<option value="{{ $o['value'] }}" @selected(request('exam_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach</select></div>
    <x-class-picker :classes="$classes" :sections="$sections" :subjects="$subjects" />
    <button class="btn btn-primary">Load students</button>
</form>

@if ($students->isNotEmpty())
    <form method="POST" action="{{ staff_route('marks.store') }}" class="card overflow-hidden">
        @csrf
        @foreach (['exam_id', 'class_id', 'section_id', 'subject_id'] as $key)<input type="hidden" name="{{ $key }}" value="{{ request($key) }}">@endforeach
        <div class="card-head flex-wrap">
            <div><h2 class="card-title">{{ $exam->name }}</h2>@if ($locked)<p class="text-xs text-rose-600 dark:text-rose-400">Results are published, so marks are locked. Ask the admin to unpublish first.</p>@endif</div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-semibold text-ink-2" for="full_marks">Full marks</label>
                <input id="full_marks" name="full_marks" type="number" min="1" max="1000" value="{{ old('full_marks', $fullMarks + 0) }}" class="input w-24" @disabled($locked) required>
                @unless ($locked)<button class="btn btn-primary">Save marks</button>@endunless
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Roll</th><th scope="col">Student</th><th scope="col">ID</th><th scope="col" class="w-40">Marks</th></tr></thead>
                <tbody>
                    @foreach ($students as $s)
                        <tr>
                            <td>{{ $s->roll }}</td><td class="font-semibold text-ink">{{ $s->name }}</td><td>{{ $s->student_no }}</td>
                            <td><input type="number" name="marks[{{ $s->id }}]" value="{{ old('marks.'.$s->id, $s->mark !== null ? $s->mark + 0 : '') }}" step="0.5" min="0" class="input" aria-label="Marks for {{ $s->name }}" @disabled($locked)>@error('marks.'.$s->id)<p class="field-error">{{ $message }}</p>@enderror</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
@else
    <div class="card"><x-empty :title="request('subject_id') ? 'No students in this section' : 'Choose an exam, class and subject'" icon="file-pen-line" /></div>
@endif
@endsection
