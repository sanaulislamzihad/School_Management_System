@extends('layouts.dashboard')
@section('title', 'Issue & return')
@section('content')
<x-page-head title="Issue & return" subtitle="Books currently with students, and their return dates.">
    <a class="btn" href="{{ route('admin.resource.index', 'books') }}">Book catalogue</a>
</x-page-head>
<form method="POST" action="{{ route('admin.library.store') }}" class="card mb-4 grid items-end gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
    @csrf
    <div><label class="field-label" for="book_id">Book</label><select id="book_id" name="book_id" class="input" required><option value="">Select book</option>@foreach ($books as $b)<option value="{{ $b->id }}">{{ $b->title }}</option>@endforeach</select>@error('book_id')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label class="field-label" for="student_id">Student</label><select id="student_id" name="student_id" class="input" required><option value="">Select student</option>@foreach ($students as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->student_no }})</option>@endforeach</select></div>
    <div><label class="field-label" for="issue_date">Issue date</label><input id="issue_date" type="date" name="issue_date" value="{{ today()->toDateString() }}" class="input" required></div>
    <div><label class="field-label" for="due_date">Return by</label><input id="due_date" type="date" name="due_date" value="{{ today()->addDays(14)->toDateString() }}" class="input" required>@error('due_date')<p class="field-error">{{ $message }}</p>@enderror</div>
    <button class="btn btn-primary">Issue book</button>
</form>
<div class="card overflow-hidden">
    @if ($issues->isEmpty())
        <x-empty title="No books issued" icon="library" />
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Book</th><th scope="col">Student</th><th scope="col">Issued</th><th scope="col">Return by</th><th scope="col">Status</th><th scope="col" class="text-right">Action</th></tr></thead>
                <tbody>
                    @foreach ($issues as $i)
                        @php($state = $i->return_date ? 'Returned' : ($i->due_date < today()->toDateString() ? 'Overdue' : 'Issued'))
                        <tr><td class="font-semibold text-ink">{{ $i->title }}</td><td><span class="block">{{ $i->student_name }}</span><span class="block text-xs text-ink-3">{{ $i->student_no }}</span></td><td>{{ nice_date($i->issue_date) }}</td><td>{{ nice_date($i->due_date) }}</td><td><span class="badge {{ status_badge($state) }}">{{ $state }}</span></td><td class="text-right">@unless ($i->return_date)<form method="POST" action="{{ route('admin.library.return', $i->id) }}">@csrf<button class="btn btn-sm">Mark returned</button></form>@endunless</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($issues->hasPages())<div class="border-t border-line px-5 py-3">{{ $issues->links() }}</div>@endif
    @endif
</div>
@endsection
