@extends('layouts.dashboard')
@section('title', 'Attendance')
@php
    $total = $records->count();
    $attended = ($counts['Present'] ?? 0) + ($counts['Late'] ?? 0);
@endphp
@section('content')
<x-page-head title="Attendance" subtitle="Your daily attendance record." />
@if ($months->isEmpty())
    <div class="card"><x-empty title="No attendance recorded yet" icon="calendar-check" /></div>
@else
    <nav class="mb-4 flex flex-wrap gap-1.5" aria-label="Month">
        @foreach ($months as $m)
            <a href="{{ route('portal.attendance', ['month' => $m]) }}" @class(['btn btn-sm', 'btn-primary' => $m === $month])>{{ \Carbon\Carbon::parse($m.'-01')->format('F Y') }}</a>
        @endforeach
    </nav>
    <div class="mb-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Present" :value="$counts['Present'] ?? 0" icon="circle-check" tone="emerald" />
        <x-stat label="Absent" :value="$counts['Absent'] ?? 0" icon="circle-x" tone="rose" />
        <x-stat label="Late or leave" :value="($counts['Late'] ?? 0) + ($counts['Leave'] ?? 0)" icon="clock" tone="amber" />
        <x-stat label="Attendance rate" :value="($total ? round($attended / $total * 100) : 0).'%'" icon="chart-line" tone="indigo" />
    </div>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th scope="col">Date</th><th scope="col">Day</th><th scope="col">Status</th></tr></thead>
            <tbody>@foreach ($records as $r)<tr><td class="font-semibold text-ink">{{ nice_date($r->date) }}</td><td>{{ \Carbon\Carbon::parse($r->date)->format('l') }}</td><td><span class="badge {{ status_badge($r->status) }}">{{ $r->status }}</span></td></tr>@endforeach</tbody>
        </table></div>
    </div>
@endif
@endsection
