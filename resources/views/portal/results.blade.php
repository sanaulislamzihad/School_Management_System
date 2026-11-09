@extends('layouts.dashboard')
@section('title', 'Results')
@section('content')
<x-page-head title="Results" subtitle="Published exam results." />
@if ($results->isEmpty())
    <div class="card"><x-empty title="No published results yet" icon="award" /></div>
@else
    <div class="space-y-5">
        @foreach ($results as $r)
            <section class="card overflow-hidden">
                <div class="card-head flex-wrap">
                    <h2 class="card-title">{{ $r['exam']->name }}</h2>
                    <div class="flex items-center gap-2"><span class="badge badge-blue">GPA {{ number_format($r['result']['gpa'], 2) }}</span><span class="badge {{ $r['result']['passed'] ? 'badge-green' : 'badge-red' }}">Grade {{ $r['result']['letter'] }}</span><span class="badge badge-gray">Position {{ $r['result']['rank'] }}</span></div>
                </div>
                <div class="overflow-x-auto"><table class="tbl"><thead><tr><th scope="col">Subject</th><th scope="col">Marks</th><th scope="col">Grade</th><th scope="col">Point</th></tr></thead>
                    <tbody>@foreach ($r['result']['subjects'] as $s)<tr><td class="font-semibold text-ink">{{ $s['subject'] }}</td><td>{{ $s['marks'] + 0 }} / {{ $s['full'] + 0 }}</td><td>{{ $s['letter'] }}</td><td>{{ number_format($s['point'], 2) }}</td></tr>@endforeach</tbody>
                </table></div>
            </section>
        @endforeach
    </div>
@endif
@endsection
