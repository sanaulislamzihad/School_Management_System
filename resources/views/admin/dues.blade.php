@extends('layouts.dashboard')
@section('title', 'Due list')
@section('content')
<x-page-head title="Due list" :subtitle="$dues->total().' unpaid invoices · '.money($outstanding).' outstanding'">
    <a class="btn" href="{{ route('admin.fees.index') }}">Back to fees</a>
</x-page-head>
<form method="GET" class="mb-4"><select name="class_id" class="input w-auto" aria-label="Class" onchange="this.form.submit()"><option value="">All classes</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}" @selected(request('class_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach</select></form>
<div class="card overflow-hidden">
    @if ($dues->isEmpty())
        <x-empty title="No dues" text="All invoices are paid." icon="circle-check" />
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Student</th><th scope="col">Invoice</th><th scope="col">Due date</th><th scope="col">Due</th><th scope="col">Guardian phone</th></tr></thead>
                <tbody>@foreach ($dues as $d)<tr><td><span class="block font-semibold text-ink">{{ $d->student_name }}</span><span class="block text-xs text-ink-3">{{ $d->student_no }} · {{ $d->class_name }}</span></td><td><span class="block">{{ $d->invoice_no }}</span><span class="block text-xs text-ink-3">{{ $d->title }} · {{ $d->period }}</span></td><td>{{ nice_date($d->due_date) }}</td><td class="font-bold text-ink">{{ money($d->amount - $d->paid) }}</td><td>{{ $d->guardian_phone ?: '-' }}</td></tr>@endforeach</tbody>
            </table>
        </div>
        @if ($dues->hasPages())<div class="border-t border-line px-5 py-3">{{ $dues->links() }}</div>@endif
    @endif
</div>
@endsection
