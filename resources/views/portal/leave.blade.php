@extends('layouts.dashboard')
@section('title', 'Leave application')
@section('content')
<x-page-head title="Leave application" subtitle="Apply for leave and follow the decision." />
<div class="grid gap-5 lg:grid-cols-[380px_1fr]">
    <form method="POST" action="{{ route('portal.leave.store') }}" class="card self-start">
        @csrf
        <h2 class="card-head card-title">New application</h2>
        <div class="space-y-4 p-5">
            <div><label class="field-label" for="type">Leave type</label><select id="type" name="type" class="input">@foreach (['Sick', 'Family', 'Other'] as $t)<option @selected(old('type') === $t)>{{ $t }}</option>@endforeach</select></div>
            <x-field name="from_date" label="From" type="date" required />
            <x-field name="to_date" label="To" type="date" required />
            <x-field name="reason" label="Short reason" required />
            <x-field name="details" label="Details" type="textarea" />
            <button class="btn btn-primary w-full">Send application</button>
        </div>
    </form>
    <section class="card overflow-hidden">
        <h2 class="card-head card-title">My applications</h2>
        @if ($leaves->isEmpty())
            <x-empty title="No applications yet" icon="calendar-off" />
        @else
            <ul class="divide-y divide-line">@foreach ($leaves as $l)<li class="flex items-center justify-between gap-3 px-5 py-3.5"><span><span class="block text-sm font-bold">{{ $l->type }}: {{ $l->reason }}</span><span class="block text-xs text-ink-2">{{ nice_date($l->from_date) }} to {{ nice_date($l->to_date) }}</span></span><span class="badge {{ status_badge($l->status) }}">{{ $l->status }}</span></li>@endforeach</ul>
        @endif
    </section>
</div>
@endsection
