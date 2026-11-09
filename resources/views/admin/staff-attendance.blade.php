@extends('layouts.dashboard')
@section('title', 'Staff attendance')
@section('content')
<x-page-head title="Staff attendance" subtitle="Daily register for teachers and office staff." />
<form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
    <div><label class="field-label" for="date">Date</label><input id="date" type="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}" class="input" onchange="this.form.submit()"></div>
</form>
<form method="POST" action="{{ route('admin.staff-attendance.store') }}" class="card overflow-hidden">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">
    <div class="card-head"><h2 class="card-title">{{ nice_date($date) }}</h2><button class="btn btn-primary">Save attendance</button></div>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th scope="col">Employee</th><th scope="col">Designation</th><th scope="col" class="text-right">Status</th></tr></thead>
            <tbody>@foreach ($employees as $e)<tr><td><span class="flex items-center gap-3 font-semibold text-ink"><x-avatar :name="$e->name" :photo="$e->photo" class="size-9" /> {{ $e->name }}</span></td><td>{{ $e->designation }}</td><td class="text-right"><x-attendance-row :id="$e->id" :value="$e->attendance" /></td></tr>@endforeach</tbody>
        </table>
    </div>
</form>
@endsection
