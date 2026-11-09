@extends('layouts.dashboard')
@section('title', 'Dashboard')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $genderTotal = max(1, $gender->sum());
    $invoiceTotal = max(1, $invoiceStatus->sum());
    $classMax = max(1, $byClass->max('total'));
@endphp

@section('content')
<section class="relative mb-5 overflow-hidden rounded-2xl p-6 text-white sm:p-7" style="background: linear-gradient(115deg, #4338ca 0%, #6d4bea 55%, #0f9d94 100%)">
    <div class="relative flex flex-wrap items-center justify-between gap-5">
        <div>
            <p class="text-sm text-indigo-100">{{ now()->format('l, F j, Y') }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $greeting }}, {{ strtok(auth()->user()->name, ' ') }}</h1>
            <p class="mt-1 text-indigo-100">Here is what is happening at {{ setting('school_name') }} today.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.students.create') }}" class="btn border-transparent bg-white text-indigo-800 hover:bg-indigo-50"><x-lucide-user-plus class="size-4" /> Add student</a>
            <a href="{{ route('admin.attendance.index') }}" class="btn border-white/30 bg-white/10 text-white hover:bg-white/20"><x-lucide-calendar-check class="size-4" /> Take attendance</a>
            <a href="{{ route('admin.fees.index') }}" class="btn border-white/30 bg-white/10 text-white hover:bg-white/20"><x-lucide-wallet class="size-4" /> Collect fees</a>
        </div>
    </div>
</section>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="School overview">
    <x-stat label="Students" :value="number_format($stats['students'])" sub="Active on roll" icon="graduation-cap" tone="indigo" :href="route('admin.students.index')" />
    <x-stat label="Staff" :value="$stats['staff']" sub="Active employees" icon="users" tone="sky" :href="route('admin.employees.index')" />
    <x-stat label="Today's attendance" :value="$stats['attendance']" :sub="$stats['markedToday'].' students marked'" icon="calendar-check" tone="teal" :href="route('admin.attendance.index')" />
    <x-stat label="Fees this month" :value="money($stats['feesMonth'])" :sub="now()->format('F Y')" icon="banknote" tone="emerald" :href="route('admin.fees.index')" />
    <x-stat label="Fees due" :value="money($stats['due'])" sub="Unpaid and partial" icon="wallet" tone="rose" :href="route('admin.fees.dues')" />
    <x-stat label="New applications" :value="$stats['pendingAdmissions']" sub="Waiting for review" icon="clipboard-list" tone="violet" :href="route('admin.admissions.index')" />
    <x-stat label="Upcoming exams" :value="$stats['upcomingExams']" sub="From today" icon="file-pen-line" tone="amber" :href="route('admin.resource.index', 'exams')" />
    <x-stat label="Leave requests" :value="$stats['pendingLeaves']" sub="Waiting for a decision" icon="calendar-off" tone="orange" :href="route('admin.student-leaves.index')" />
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-5">
    <div class="card xl:col-span-3">
        <div class="card-head"><div><h2 class="card-title">Attendance rate</h2><p class="text-xs text-ink-3">Last 14 school days, percent of students present or late</p></div></div>
        <div class="p-5"><x-line-chart :points="$attendance" title="Attendance rate for the last 14 school days" :max="100" /></div>
    </div>
    <div class="card xl:col-span-2">
        <div class="card-head"><div><h2 class="card-title">Fee collection</h2><p class="text-xs text-ink-3">Last 6 months, in taka</p></div><a href="{{ route('admin.fees.index') }}" class="text-sm font-semibold text-primary-ink">View</a></div>
        <div class="p-5"><x-bar-chart :points="$fees" title="Fee collection for the last 6 months" /></div>
    </div>
</section>

<section class="mt-5 grid gap-5 lg:grid-cols-3">
    <div class="card lg:col-span-2">
        <div class="card-head"><div><h2 class="card-title">Students by class</h2><p class="text-xs text-ink-3">Active students this session</p></div><a href="{{ route('admin.students.index') }}" class="text-sm font-semibold text-primary-ink">All students</a></div>
        <ul class="grid gap-x-8 gap-y-3 p-5 sm:grid-cols-2">
            @foreach ($byClass as $row)
                <li class="flex items-center gap-3 text-sm">
                    <span class="w-16 shrink-0 font-semibold text-ink">{{ $row->name }}</span>
                    <span class="h-2 flex-1 overflow-hidden rounded-full bg-primary-soft"><span class="block h-full rounded-full bg-primary" style="width: {{ $row->total / $classMax * 100 }}%"></span></span>
                    <span class="w-7 text-right font-bold text-ink">{{ $row->total }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="space-y-5">
        <div class="card">
            <div class="card-head"><h2 class="card-title">Students by gender</h2></div>
            <ul class="space-y-3 p-5 text-sm">
                @foreach (['Male' => 'Boys', 'Female' => 'Girls'] as $key => $label)
                    @php($n = $gender[$key] ?? 0)
                    <li class="flex items-center justify-between"><span class="text-ink-2">{{ $label }}</span><span class="font-bold text-ink">{{ $n }} <span class="font-normal text-ink-3">({{ round($n / $genderTotal * 100) }}%)</span></span></li>
                @endforeach
            </ul>
        </div>
        <div class="card">
            <div class="card-head"><h2 class="card-title">Invoice status</h2></div>
            <ul class="space-y-3 p-5 text-sm">
                @foreach (['Paid', 'Partial', 'Unpaid'] as $state)
                    @php($n = $invoiceStatus[$state] ?? 0)
                    <li class="flex items-center justify-between"><span class="badge {{ status_badge($state) }}">{{ $state }}</span><span class="font-bold text-ink">{{ $n }} <span class="font-normal text-ink-3">({{ round($n / $invoiceTotal * 100) }}%)</span></span></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="mt-5 grid gap-5 lg:grid-cols-3">
    <div class="card">
        <div class="card-head"><h2 class="card-title">New applications</h2><a href="{{ route('admin.admissions.index') }}" class="text-sm font-semibold text-primary-ink">View</a></div>
        <ul class="divide-y divide-line">
            @forelse ($admissions as $a)
                <li class="flex items-center gap-3 px-5 py-3"><x-avatar :name="$a->name" class="size-9" /><span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold">{{ $a->name }}</span><span class="block text-xs text-ink-3">{{ $a->class_name }} · {{ $a->reference }}</span></span><span class="badge {{ status_badge($a->status) }}">{{ $a->status }}</span></li>
            @empty
                <li><x-empty title="No applications" icon="clipboard-list" /></li>
            @endforelse
        </ul>
    </div>
    <div class="card">
        <div class="card-head"><h2 class="card-title">Recent payments</h2><a href="{{ route('admin.fees.index') }}" class="text-sm font-semibold text-primary-ink">View</a></div>
        <ul class="divide-y divide-line">
            @forelse ($payments as $p)
                <li class="flex items-center gap-3 px-5 py-3"><x-avatar :name="$p->student_name" class="size-9" /><span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold">{{ $p->student_name }}</span><span class="block text-xs text-ink-3">{{ $p->method }} · {{ nice_date($p->paid_on) }}</span></span><span class="text-sm font-bold">{{ money($p->amount) }}</span></li>
            @empty
                <li><x-empty title="No payments yet" icon="wallet" /></li>
            @endforelse
        </ul>
    </div>
    <div class="card">
        <div class="card-head"><h2 class="card-title">Notices</h2><a href="{{ route('admin.resource.index', 'notices') }}" class="text-sm font-semibold text-primary-ink">View</a></div>
        <ul class="divide-y divide-line">
            @foreach ($notices as $n)
                <li class="flex items-start gap-3 px-5 py-3"><span class="tone-rose inline-flex size-9 shrink-0 items-center justify-center rounded-lg"><x-lucide-megaphone class="size-4" /></span><span class="min-w-0"><span class="block text-sm font-semibold leading-snug">{{ $n->title }}</span><span class="block text-xs text-ink-3">{{ nice_date($n->date) }}</span></span></li>
            @endforeach
        </ul>
    </div>
</section>
@endsection
