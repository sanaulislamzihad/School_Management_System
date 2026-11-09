@extends('layouts.dashboard')
@section('title', 'Promotions')
@section('content')
<x-page-head title="Promotions" subtitle="Move students to their next class and session." />

<form method="GET" class="card mb-4 grid items-end gap-3 p-4 sm:grid-cols-4">
    <div>
        <label class="field-label" for="session_id">Current session</label>
        <select id="session_id" name="session_id" class="input" required><option value="">Select session</option>@foreach ($sessions as $o)<option value="{{ $o['value'] }}" @selected(request('session_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach</select>
    </div>
    <x-class-picker :classes="$classes" :sections="$sections" />
    <button class="btn btn-primary">Load students</button>
</form>

@if ($students->isNotEmpty())
    <form method="POST" action="{{ route('admin.promotions.store') }}" class="card" x-data="{ toClass: '', sections: @js($sections), all: true }">
        @csrf
        <div class="grid items-end gap-3 border-b border-line p-4 sm:grid-cols-4">
            <div><label class="field-label" for="to_session_id">Promote to session</label><select id="to_session_id" name="to_session_id" class="input" required><option value="">Select</option>@foreach ($sessions as $o)<option value="{{ $o['value'] }}">{{ $o['label'] }}</option>@endforeach</select></div>
            <div><label class="field-label" for="to_class_id">Promote to class</label><select id="to_class_id" name="to_class_id" class="input" x-model="toClass" required><option value="">Select</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}">{{ $o['label'] }}</option>@endforeach</select></div>
            <div><label class="field-label" for="to_section_id">Section</label><select id="to_section_id" name="to_section_id" class="input" required><option value="">Select</option><template x-for="o in sections.filter(s => String(s.parent) === toClass)" :key="o.value"><option :value="o.value" x-text="o.label"></option></template></select></div>
            <button class="btn btn-primary">Promote selected</button>
        </div>
        @error('student_ids') <p class="field-error px-4 pt-3">{{ $message }}</p> @enderror
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col"><input type="checkbox" x-model="all" aria-label="Select all" class="size-4 accent-indigo-600"></th><th scope="col">Roll</th><th scope="col">Student</th><th scope="col">ID</th></tr></thead>
                <tbody>@foreach ($students as $s)<tr><td><input type="checkbox" name="student_ids[]" value="{{ $s->id }}" :checked="all" aria-label="Select {{ $s->name }}" class="size-4 accent-indigo-600"></td><td>{{ $s->roll }}</td><td class="font-semibold text-ink">{{ $s->name }}</td><td>{{ $s->student_no }}</td></tr>@endforeach</tbody>
            </table>
        </div>
    </form>
@elseif (request('section_id'))
    <div class="card"><x-empty title="No students in this section" icon="graduation-cap" /></div>
@endif
@endsection
