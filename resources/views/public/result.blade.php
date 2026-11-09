@extends('layouts.public')
@section('title', 'Exam Results')
@section('content')
<x-web-page title="Exam Results">
    <form method="POST" action="{{ route('result.lookup') }}" class="grid max-w-3xl gap-4 rounded-xl border border-slate-200 p-5 sm:grid-cols-3">
        @csrf
        <div><label for="e" class="mb-1 block text-sm font-semibold">Exam</label><select id="e" name="exam_id" class="w-full rounded-lg border border-slate-300 px-3 py-2" required><option value="">Select exam</option>@foreach ($exams as $ex)<option value="{{ $ex->id }}" @selected(old('exam_id', request('exam_id')) == $ex->id)>{{ $ex->name }}</option>@endforeach</select></div>
        <div><label for="n" class="mb-1 block text-sm font-semibold">Student ID</label><input id="n" name="student_no" value="{{ old('student_no', request('student_no')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
        <div><label for="d" class="mb-1 block text-sm font-semibold">Date of birth</label><input id="d" type="date" name="dob" value="{{ old('dob', request('dob')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
        <button class="rounded-lg bg-navy px-5 py-2.5 font-semibold text-white sm:col-span-3 sm:justify-self-start">Find result</button>
    </form>
    @if ($searched && ! $result)
        <p class="mt-5 rounded-lg bg-amber-50 p-4 text-amber-900">No result found. Please check the student ID and date of birth.</p>
    @elseif ($result)
        <section class="mt-6 max-w-3xl overflow-hidden rounded-xl border border-slate-200">
            <div class="flex flex-wrap justify-between gap-3 bg-sky-50 p-5">
                <div><h2 class="text-xl font-bold text-navy">{{ $student->name }}</h2><p class="text-sm text-slate-600">ID {{ $student->student_no }} · {{ $exam->name }}</p></div>
                <div class="text-right"><p class="text-3xl font-extrabold text-navy">{{ number_format($result['gpa'], 2) }}</p><p class="text-sm font-semibold">GPA · Grade {{ $result['letter'] }}</p></div>
            </div>
            <table class="w-full text-sm"><thead class="text-left"><tr><th scope="col" class="px-5 py-2.5">Subject</th><th scope="col" class="px-5 py-2.5">Marks</th><th scope="col" class="px-5 py-2.5">Grade</th></tr></thead><tbody class="divide-y divide-slate-200">@foreach ($result['subjects'] as $s)<tr><td class="px-5 py-2.5">{{ $s['subject'] }}</td><td class="px-5 py-2.5">{{ $s['marks'] + 0 }} / {{ $s['full'] + 0 }}</td><td class="px-5 py-2.5 font-semibold">{{ $s['letter'] }}</td></tr>@endforeach</tbody></table>
        </section>
    @endif
</x-web-page>
@endsection
