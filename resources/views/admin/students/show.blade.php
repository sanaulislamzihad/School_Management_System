@extends('layouts.dashboard')
@section('title', $student->name)
@php
    $total = max(1, $attendance->sum());
    $rate = round((($attendance['Present'] ?? 0) + ($attendance['Late'] ?? 0)) / $total * 100);
    $info = [
        'Gender' => $student->gender, 'Date of birth' => nice_date($student->dob), 'Blood group' => $student->blood_group ?: '-', 'Religion' => $student->religion ?: '-',
        'Phone' => $student->phone ?: '-', 'Session' => $student->session->name, 'Shift' => $student->shift->name ?? '-', 'Admitted on' => nice_date($student->admission_date),
        'Father' => $student->father_name ?: '-', 'Mother' => $student->mother_name ?: '-', 'Guardian' => $student->guardian_name ?: '-', 'Guardian phone' => $student->guardian_phone ?: '-', 'Address' => $student->present_address ?: '-',
    ];
@endphp
@section('content')
<div x-data="{ confirmDelete: false }">
    <x-page-head :title="$student->name" :subtitle="$student->schoolClass->name.' '.($student->section->name ?? '').' · ID '.$student->student_no">
        <form method="POST" action="{{ route('admin.students.reset-password', $student) }}">@csrf<button class="btn"><x-lucide-key-round class="size-4" /> Reset password</button></form>
        <a class="btn" href="{{ route('admin.students.edit', $student) }}"><x-lucide-pencil class="size-4" /> Edit</a>
        <button type="button" class="btn text-rose-600 dark:text-rose-400" @click="confirmDelete = true"><x-lucide-trash-2 class="size-4" /> Delete</button>
    </x-page-head>

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="card p-6 text-center">
            <x-avatar :name="$student->name" :photo="$student->photo" class="mx-auto size-24 text-2xl" />
            <h2 class="mt-3 text-lg font-bold">{{ $student->name }}</h2>
            <p class="text-sm text-ink-2">Roll {{ $student->roll ?? '-' }}</p>
            <span class="badge mt-2 {{ status_badge($student->status) }}">{{ $student->status }}</span>
            <div class="mt-5 grid grid-cols-2 gap-3 border-t border-line pt-4">
                <div><p class="text-2xl font-extrabold">{{ $rate }}%</p><p class="text-xs text-ink-3">Attendance</p></div>
                <div><p class="text-2xl font-extrabold">{{ $results->count() }}</p><p class="text-xs text-ink-3">Exams</p></div>
            </div>
        </section>
        <section class="card lg:col-span-2">
            <h2 class="card-head card-title">Details</h2>
            <dl class="grid gap-x-8 gap-y-3 p-5 sm:grid-cols-2">
                @foreach ($info as $label => $value)
                    <div class="flex justify-between gap-4 text-sm"><dt class="text-ink-2">{{ $label }}</dt><dd class="text-right font-semibold">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </section>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <section class="card overflow-hidden">
            <h2 class="card-head card-title">Exam results</h2>
            @if ($results->isEmpty())
                <x-empty title="No results yet" icon="award" />
            @else
                <table class="tbl"><thead><tr><th scope="col">Exam</th><th scope="col">GPA</th><th scope="col">Grade</th><th scope="col">Position</th></tr></thead>
                    <tbody>@foreach ($results as $r)<tr><td class="font-semibold text-ink">{{ $r['exam']->name }}</td><td>{{ number_format($r['result']['gpa'], 2) }}</td><td>{{ $r['result']['letter'] }}</td><td>{{ $r['result']['rank'] }}</td></tr>@endforeach</tbody>
                </table>
            @endif
        </section>
        <section class="card overflow-hidden">
            <h2 class="card-head card-title">Recent invoices</h2>
            @if ($invoices->isEmpty())
                <x-empty title="No invoices yet" icon="wallet" />
            @else
                <div class="overflow-x-auto"><table class="tbl"><thead><tr><th scope="col">Fee</th><th scope="col">Period</th><th scope="col">Due</th><th scope="col">Status</th></tr></thead>
                    <tbody>@foreach ($invoices as $i)<tr><td class="font-semibold text-ink">{{ $i->title }}</td><td>{{ $i->period }}</td><td>{{ money($i->due()) }}</td><td><span class="badge {{ status_badge($i->status) }}">{{ $i->status }}</span></td></tr>@endforeach</tbody>
                </table></div>
            @endif
        </section>
    </div>

    <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="alertdialog" aria-modal="true">
        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="card w-full max-w-sm p-6">
            @csrf @method('DELETE')
            <h2 class="text-lg font-bold">Delete {{ $student->name }}?</h2>
            <p class="mt-1 text-sm text-ink-2">Attendance, marks and fee records for this student will also be removed. This cannot be undone.</p>
            <div class="mt-5 flex justify-end gap-2"><button type="button" class="btn" @click="confirmDelete = false">Cancel</button><button class="btn btn-danger">Delete</button></div>
        </form>
    </div>
</div>
@endsection
