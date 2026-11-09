@extends('layouts.dashboard')
@section('title', 'Profile')
@section('content')
<x-page-head title="My profile" />
<div class="grid gap-5 lg:grid-cols-2">
    <section class="card p-6">
        <div class="flex items-center gap-4"><x-avatar :name="$me->name" :photo="$me->photo" class="size-20 text-xl" /><div><h2 class="text-xl font-bold">{{ $me->name }}</h2><p class="text-sm text-ink-2">{{ $me->designation }}</p></div></div>
        <dl class="mt-5 space-y-3 border-t border-line pt-4 text-sm">
            @foreach (['Employee ID' => $me->employee_no, 'Department' => $me->department, 'Phone' => $me->phone, 'Email' => $me->email, 'Joined' => nice_date($me->joining_date)] as $label => $value)
                <div class="flex justify-between gap-4"><dt class="text-ink-2">{{ $label }}</dt><dd class="text-right font-semibold">{{ $value ?: '-' }}</dd></div>
            @endforeach
        </dl>
    </section>
    <x-password-form />
</div>
@endsection
