@extends('layouts.dashboard')
@section('title', 'Class tests')
@section('content')
<x-page-head title="Class tests" subtitle="Your class test marks." />
<div class="card overflow-hidden">
    @if ($tests->isEmpty())
        <x-empty title="No class tests yet" icon="clipboard-check" />
    @else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th scope="col">Test</th><th scope="col">Subject</th><th scope="col">Date</th><th scope="col">Marks</th></tr></thead>
            <tbody>@foreach ($tests as $t)<tr><td class="font-semibold text-ink">{{ $t->title }}</td><td>{{ $t->subject_name }}</td><td>{{ nice_date($t->date) }}</td><td>{{ $t->marks !== null ? ($t->marks + 0).' / '.($t->full_marks + 0) : 'Not entered' }}</td></tr>@endforeach</tbody>
        </table></div>
    @endif
</div>
@endsection
