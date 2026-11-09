@extends('layouts.dashboard')
@section('title', 'Profile')
@section('content')
<x-page-head title="My profile" subtitle="Your details and password." />
<div class="grid gap-5 lg:grid-cols-2">
    <section class="card p-6">
        <div class="flex items-center gap-4"><x-avatar :name="$me->name" :photo="$me->photo" class="size-20 text-xl" /><div><h2 class="text-xl font-bold">{{ $me->name }}</h2><p class="text-sm text-ink-2">{{ $me->student_no }} · {{ $me->schoolClass->name }} {{ $me->section->name ?? '' }}</p></div></div>
        <dl class="mt-5 space-y-3 border-t border-line pt-4 text-sm">
            @foreach (['Roll' => $me->roll, 'Session' => $me->session->name, 'Shift' => $me->shift->name ?? null, 'Gender' => $me->gender, 'Date of birth' => nice_date($me->dob), 'Blood group' => $me->blood_group, 'Phone' => $me->phone, 'Father' => $me->father_name, 'Mother' => $me->mother_name, 'Guardian phone' => $me->guardian_phone, 'Address' => $me->present_address] as $label => $value)
                <div class="flex justify-between gap-4"><dt class="text-ink-2">{{ $label }}</dt><dd class="text-right font-semibold">{{ $value ?: '-' }}</dd></div>
            @endforeach
        </dl>
    </section>
    <x-password-form />
</div>
@endsection
