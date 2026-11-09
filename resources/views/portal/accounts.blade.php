@extends('layouts.dashboard')
@section('title', 'Fees')
@section('content')
<x-page-head title="Fees" subtitle="Your invoices and payments. Please pay at the school office." />
<div class="mb-4 grid gap-4 sm:grid-cols-3">
    <x-stat label="Total due" :value="money($billed - $paid)" icon="wallet" tone="rose" />
    <x-stat label="Paid" :value="money($paid)" icon="circle-check" tone="emerald" />
    <x-stat label="Total billed" :value="money($billed)" icon="receipt-text" tone="indigo" />
</div>
<div class="card overflow-hidden">
    @if ($invoices->isEmpty())
        <x-empty title="No invoices yet" icon="wallet" />
    @else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th scope="col">Fee</th><th scope="col">Period</th><th scope="col">Amount</th><th scope="col">Paid</th><th scope="col">Due</th><th scope="col">Last date</th><th scope="col">Status</th></tr></thead>
            <tbody>@foreach ($invoices as $i)<tr><td><span class="block font-semibold text-ink">{{ $i->title }}</span><span class="block text-xs text-ink-3">{{ $i->invoice_no }}</span></td><td>{{ $i->period }}</td><td>{{ money($i->amount) }}</td><td>{{ money($i->paid) }}</td><td class="font-bold text-ink">{{ money($i->amount - $i->paid) }}</td><td>{{ nice_date($i->due_date) }}</td><td><span class="badge {{ status_badge($i->status) }}">{{ $i->status }}</span></td></tr>@endforeach</tbody>
        </table></div>
    @endif
</div>
@endsection
