@extends('layouts.dashboard')
@section('title', $test->title)
@section('content')
<x-page-head :title="$test->title" :subtitle="$test->class_name.' '.$test->section_name.' · '.$test->subject_name.' · '.nice_date($test->date)">
    <a class="btn" href="{{ staff_route('resource.index', ['resource' => 'class-tests']) }}"><x-lucide-arrow-left class="size-4" /> Back</a>
</x-page-head>
<form method="POST" action="{{ staff_route('class-test-marks.update', ['classTest' => $test->id]) }}" class="card overflow-hidden">
    @csrf
    <div class="card-head"><h2 class="card-title">Full marks: {{ $test->full_marks + 0 }}</h2><button class="btn btn-primary">Save marks</button></div>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th scope="col">Roll</th><th scope="col">Student</th><th scope="col" class="w-40">Marks</th></tr></thead>
            <tbody>
                @foreach ($students as $s)
                    <tr><td>{{ $s->roll }}</td><td class="font-semibold text-ink">{{ $s->name }}</td><td><input type="number" name="marks[{{ $s->id }}]" value="{{ old('marks.'.$s->id, $s->mark !== null ? $s->mark + 0 : '') }}" min="0" max="{{ $test->full_marks + 0 }}" step="0.5" class="input" aria-label="Marks for {{ $s->name }}">@error('marks.'.$s->id)<p class="field-error">{{ $message }}</p>@enderror</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
@endsection
