@extends('layouts.dashboard')
@section('title', 'Payroll')
@section('content')
<x-page-head title="Payroll" subtitle="Monthly salary slips and payments.">
    <form method="POST" action="{{ route('admin.payroll.generate') }}">@csrf<input type="hidden" name="month" value="{{ $month }}"><button class="btn btn-primary"><x-lucide-plus class="size-4" /> Generate slips</button></form>
</x-page-head>
<form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
    <div><label class="field-label" for="month">Month</label><input id="month" type="month" name="month" value="{{ $month }}" class="input" onchange="this.form.submit()"></div>
    <p class="pb-2.5 text-sm text-ink-2">Total net pay: <strong class="text-ink">{{ money($slips->sum('net')) }}</strong></p>
</form>
<div class="card overflow-hidden">
    @if ($slips->isEmpty())
        <x-empty title="No salary slips for this month" text="Use Generate slips to create them." icon="wallet" />
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Employee</th><th scope="col">Basic</th><th scope="col">Allowance</th><th scope="col">Deduction</th><th scope="col">Net pay</th><th scope="col">Status</th><th scope="col" class="text-right">Action</th></tr></thead>
                <tbody>@foreach ($slips as $s)<tr><td><span class="block font-semibold text-ink">{{ $s->name }}</span><span class="block text-xs text-ink-3">{{ $s->designation }}</span></td><td>{{ money($s->basic) }}</td><td>{{ money($s->allowance) }}</td><td>{{ money($s->deduction) }}</td><td class="font-bold text-ink">{{ money($s->net) }}</td><td><span class="badge {{ status_badge($s->status) }}">{{ $s->status }}</span></td><td class="text-right">@if ($s->status === 'Unpaid')<form method="POST" action="{{ route('admin.payroll.pay', $s->id) }}">@csrf<button class="btn btn-sm btn-primary">Mark paid</button></form>@else<span class="text-ink-3">{{ nice_date($s->paid_on) }}</span>@endif</td></tr>@endforeach</tbody>
            </table>
        </div>
    @endif
</div>
@endsection
