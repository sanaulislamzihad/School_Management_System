@extends('layouts.dashboard')
@section('title', 'Fees')
@section('content')
<div x-data="{ collect: null, newInvoice: false, generate: false }">
    <x-page-head title="Fees" subtitle="Create invoices and record cash, bKash, Nagad or bank payments.">
        <a class="btn" href="{{ route('admin.fees.dues') }}">Due list</a>
        <a class="btn" href="{{ route('admin.resource.index', 'fee-heads') }}">Fee heads</a>
        <button type="button" class="btn" @click="generate = true">Generate monthly</button>
        <button type="button" class="btn btn-primary" @click="newInvoice = true"><x-lucide-plus class="size-4" /> New invoice</button>
    </x-page-head>

    <div class="mb-4 grid gap-4 sm:grid-cols-2">
        <x-stat label="Collected" :value="money($collected)" icon="banknote" tone="emerald" />
        <x-stat label="Outstanding" :value="money($outstanding)" icon="wallet" tone="rose" :href="route('admin.fees.dues')" />
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2.5">
        <label class="relative min-w-56 flex-1 sm:max-w-sm"><span class="sr-only">Search</span><x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-3" /><input class="input pl-10" type="search" name="q" value="{{ request('q') }}" placeholder="Invoice, student or ID"></label>
        <select name="status" class="input w-auto" aria-label="Status" onchange="this.form.submit()"><option value="">All statuses</option>@foreach (['Unpaid', 'Partial', 'Paid'] as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select>
        <select name="class_id" class="input w-auto" aria-label="Class" onchange="this.form.submit()"><option value="">All classes</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}" @selected(request('class_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach</select>
    </form>

    @if ($errors->any())<div class="mb-4 rounded-xl bg-rose-50 p-4 text-sm font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200" role="alert">{{ $errors->first() }}</div>@endif

    <div class="card overflow-hidden">
        @if ($invoices->isEmpty())
            <x-empty title="No invoices found" icon="wallet" />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th scope="col">Invoice</th><th scope="col">Student</th><th scope="col">Amount</th><th scope="col">Due</th><th scope="col">Status</th><th scope="col" class="text-right">Action</th></tr></thead>
                    <tbody>
                        @foreach ($invoices as $i)
                            @php($due = round($i->amount - $i->paid, 2))
                            <tr>
                                <td><span class="block font-semibold text-ink">{{ $i->invoice_no }}</span><span class="block text-xs text-ink-3">{{ $i->title }} · {{ $i->period }}</span></td>
                                <td><span class="block">{{ $i->student_name }}</span><span class="block text-xs text-ink-3">{{ $i->student_no }} · {{ $i->class_name }}</span></td>
                                <td>{{ money($i->amount) }}</td>
                                <td><span class="block font-semibold text-ink">{{ money($due) }}</span><span class="block text-xs text-ink-3">{{ nice_date($i->due_date) }}</span></td>
                                <td><span class="badge {{ status_badge($i->status) }}">{{ $i->status }}</span></td>
                                <td class="text-right">@if ($i->status !== 'Paid')<button type="button" class="btn btn-sm btn-primary" @click="collect = @js(['id' => $i->id, 'no' => $i->invoice_no, 'name' => $i->student_name, 'due' => $due])">Collect</button>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())<div class="border-t border-line px-5 py-3">{{ $invoices->links() }}</div>@endif
        @endif
    </div>

    <div x-show="collect" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true">
        <form method="POST" :action="collect && `{{ url('admin/fees') }}/${collect.id}/collect`" class="card w-full max-w-md p-6">
            @csrf
            <h2 class="text-lg font-bold">Collect payment</h2>
            <p class="mt-1 text-sm text-ink-2"><span x-text="collect?.name"></span> · <span x-text="collect?.no"></span></p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><label class="field-label" for="amount">Amount</label><input id="amount" type="number" step="0.01" min="1" name="amount" :max="collect?.due" :value="collect?.due" class="input" required></div>
                <div><label class="field-label" for="method">Method</label><select id="method" name="method" class="input">@foreach ($methods as $m)<option>{{ $m }}</option>@endforeach</select></div>
                <div><label class="field-label" for="paid_on">Date</label><input id="paid_on" type="date" name="paid_on" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" class="input" required></div>
                <div><label class="field-label" for="reference">Reference (optional)</label><input id="reference" name="reference" class="input"></div>
            </div>
            <div class="mt-5 flex justify-end gap-2"><button type="button" class="btn" @click="collect = null">Cancel</button><button class="btn btn-primary">Record payment</button></div>
        </form>
    </div>

    <div x-show="newInvoice" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('admin.fees.invoice') }}" class="card w-full max-w-md p-6">
            @csrf
            <h2 class="text-lg font-bold">New invoice</h2>
            <div class="mt-4 grid gap-4">
                <div><label class="field-label" for="student_id">Student</label><select id="student_id" name="student_id" class="input" required><option value="">Select student</option>@foreach ($students as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->student_no }})</option>@endforeach</select></div>
                <div><label class="field-label" for="fee_head_id">Fee head</label><select id="fee_head_id" name="fee_head_id" class="input" required><option value="">Select</option>@foreach ($heads as $h)<option value="{{ $h->id }}">{{ $h->name }} ({{ money($h->amount) }})</option>@endforeach</select></div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="field-label" for="inv-amount">Amount</label><input id="inv-amount" type="number" name="amount" min="1" step="0.01" class="input" required></div>
                    <div><label class="field-label" for="period">Period</label><input id="period" name="period" value="{{ now()->format('Y-m') }}" class="input" required></div>
                    <div><label class="field-label" for="due_date">Due date</label><input id="due_date" type="date" name="due_date" class="input" required></div>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2"><button type="button" class="btn" @click="newInvoice = false">Cancel</button><button class="btn btn-primary">Create invoice</button></div>
        </form>
    </div>

    <div x-show="generate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('admin.fees.generate') }}" class="card w-full max-w-md p-6">
            @csrf
            <h2 class="text-lg font-bold">Generate monthly invoices</h2>
            <p class="mt-1 text-sm text-ink-2">Students who already have this fee for the month are skipped.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="field-label" for="gen-head">Fee head</label><select id="gen-head" name="fee_head_id" class="input" required>@foreach ($heads as $h)<option value="{{ $h->id }}">{{ $h->name }} ({{ money($h->amount) }})</option>@endforeach</select></div>
                <div><label class="field-label" for="gen-month">Month</label><input id="gen-month" type="month" name="month" value="{{ now()->format('Y-m') }}" class="input" required></div>
                <div><label class="field-label" for="gen-due">Due date</label><input id="gen-due" type="date" name="due_date" value="{{ now()->day(10)->toDateString() }}" class="input" required></div>
                <div class="sm:col-span-2"><label class="field-label" for="gen-class">Class</label><select id="gen-class" name="class_id" class="input"><option value="">All classes</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}">{{ $o['label'] }}</option>@endforeach</select></div>
            </div>
            <div class="mt-5 flex justify-end gap-2"><button type="button" class="btn" @click="generate = false">Cancel</button><button class="btn btn-primary">Generate</button></div>
        </form>
    </div>
</div>
@endsection
