@extends('layouts.dashboard')
@section('title', 'My students')
@section('content')
<x-page-head title="My students" :subtitle="$students->total().' students in your assigned sections'" />
<form method="GET" class="mb-4 flex flex-wrap gap-2.5">
    <label class="relative min-w-56 flex-1 sm:max-w-sm"><span class="sr-only">Search</span><x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-3" /><input class="input pl-10" type="search" name="q" value="{{ request('q') }}" placeholder="Name, ID or phone"></label>
    <select name="section_id" class="input w-auto" aria-label="Section" onchange="this.form.submit()"><option value="">All my sections</option>@foreach ($sections as $s)<option value="{{ $s->section_id }}" @selected(request('section_id') == $s->section_id)>{{ $s->class_name }} {{ $s->section_name }}</option>@endforeach</select>
</form>
<div class="card overflow-hidden">
    @if ($students->isEmpty())
        <x-empty title="No students found" icon="graduation-cap" />
    @else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th scope="col">Student</th><th scope="col">Class</th><th scope="col">Roll</th><th scope="col">ID</th><th scope="col">Guardian</th></tr></thead>
            <tbody>@foreach ($students as $s)<tr><td><span class="flex items-center gap-3 font-semibold text-ink"><x-avatar :name="$s->name" class="size-9" /> {{ $s->name }}</span></td><td>{{ $s->class_name }} {{ $s->section_name }}</td><td>{{ $s->roll }}</td><td>{{ $s->student_no }}</td><td><span class="block">{{ $s->guardian_name }}</span><span class="block text-xs text-ink-3">{{ $s->guardian_phone }}</span></td></tr>@endforeach</tbody>
        </table></div>
        @if ($students->hasPages())<div class="border-t border-line px-5 py-3">{{ $students->links() }}</div>@endif
    @endif
</div>
@endsection
