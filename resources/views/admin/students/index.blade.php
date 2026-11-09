@extends('layouts.dashboard')
@section('title', 'Students')
@section('content')
<x-page-head title="Student list" :subtitle="$students->total().' students found'">
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><x-lucide-plus class="size-4" /> Add student</a>
</x-page-head>

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" x-data="{ classId: @js((string) request('class_id')), sections: @js($sections) }">
    <label class="relative"><span class="sr-only">Search</span>
        <x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-3" />
        <input class="input pl-10" type="search" name="q" value="{{ request('q') }}" placeholder="Name, ID or phone">
    </label>
    <select class="input" name="session_id" aria-label="Session" onchange="this.form.submit()">
        <option value="">All sessions</option>
        @foreach ($sessions as $o)<option value="{{ $o['value'] }}" @selected(request('session_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach
    </select>
    <select class="input" name="class_id" aria-label="Class" x-model="classId" onchange="this.form.section_id.value=''; this.form.submit()">
        <option value="">All classes</option>
        @foreach ($classes as $o)<option value="{{ $o['value'] }}" @selected(request('class_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach
    </select>
    <select class="input" name="section_id" aria-label="Section" onchange="this.form.submit()">
        <option value="">All sections</option>
        <template x-for="o in sections.filter(s => String(s.parent) === classId)" :key="o.value"><option :value="o.value" x-text="o.label" :selected="String(o.value) === @js((string) request('section_id'))"></option></template>
    </select>
    <select class="input" name="status" aria-label="Status" onchange="this.form.submit()">
        <option value="">Any status</option>
        @foreach (['Active', 'Inactive', 'Transferred', 'Passed Out'] as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach
    </select>
</form>

<div class="card overflow-hidden">
    @if ($students->isEmpty())
        <x-empty title="No students found" text="Try changing the filters, or add a new student." icon="graduation-cap" />
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Student</th><th scope="col">Class</th><th scope="col">ID</th><th scope="col">Roll</th><th scope="col">Guardian</th><th scope="col">Fees paid</th><th scope="col">Status</th><th scope="col" class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($students as $s)
                        @php($pct = $s->billed > 0 ? round($s->paid / $s->billed * 100) : 0)
                        <tr>
                            <td><a href="{{ route('admin.students.show', $s->id) }}" class="flex items-center gap-3 font-semibold text-ink hover:text-primary-ink"><x-avatar :name="$s->name" :photo="$s->photo" class="size-9" /> {{ $s->name }}</a></td>
                            <td class="whitespace-nowrap">{{ $s->class_name }} @if($s->section_name)- {{ $s->section_name }}@endif</td>
                            <td>{{ $s->student_no }}</td>
                            <td>{{ $s->roll ?? '-' }}</td>
                            <td><span class="block">{{ $s->guardian_name ?: '-' }}</span><span class="block text-xs text-ink-3">{{ $s->guardian_phone }}</span></td>
                            <td><span class="flex w-28 items-center gap-2"><span class="h-1.5 flex-1 overflow-hidden rounded-full bg-primary-soft"><span class="block h-full rounded-full bg-primary" style="width: {{ $pct }}%"></span></span><span class="text-xs font-semibold">{{ $pct }}%</span></span></td>
                            <td><span class="badge {{ status_badge($s->status) }}">{{ $s->status }}</span></td>
                            <td class="whitespace-nowrap text-right">
                                <a class="btn btn-sm" href="{{ route('admin.students.show', $s->id) }}" aria-label="View {{ $s->name }}"><x-lucide-eye class="size-4" /></a>
                                <a class="btn btn-sm" href="{{ route('admin.students.edit', $s->id) }}" aria-label="Edit {{ $s->name }}"><x-lucide-pencil class="size-4" /></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())<div class="border-t border-line px-5 py-3">{{ $students->links() }}</div>@endif
    @endif
</div>
@endsection
