@extends('layouts.dashboard')
@section('title', 'ID card')
@section('content')
<x-page-head title="Student ID card" subtitle="Print this card or save it as a PDF.">
    <button type="button" class="btn btn-primary" onclick="window.print()"><x-lucide-printer class="size-4" /> Print card</button>
</x-page-head>
<div class="mx-auto w-80 overflow-hidden rounded-2xl border border-line bg-white text-slate-800 shadow-lg">
    <div class="flex items-center gap-3 p-4 text-white" style="background: linear-gradient(115deg, #4338ca, #0f9d94); print-color-adjust: exact; -webkit-print-color-adjust: exact">
        <img src="{{ asset('logo.svg') }}" alt="" class="size-11">
        <div class="leading-tight"><p class="text-lg font-extrabold">{{ setting('school_name') }}</p><p class="text-[11px]">{{ setting('address') }}</p></div>
    </div>
    <div class="p-5 text-center">
        <x-avatar :name="$me->name" :photo="$me->photo" class="mx-auto size-24 text-2xl" />
        <h2 class="mt-3 text-lg font-extrabold">{{ $me->name }}</h2>
        <p class="text-sm font-semibold text-indigo-700">Student</p>
        <dl class="mt-4 space-y-1.5 text-left text-sm">
            @foreach (['ID' => $me->student_no, 'Class' => $me->schoolClass->name.' '.($me->section->name ?? ''), 'Roll' => $me->roll, 'Blood group' => $me->blood_group ?: '-', 'Guardian' => $me->guardian_phone, 'Valid until' => $validUntil->format('M j, Y')] as $label => $value)
                <div class="flex justify-between gap-3"><dt class="text-slate-600">{{ $label }}</dt><dd class="font-bold">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>
@endsection
