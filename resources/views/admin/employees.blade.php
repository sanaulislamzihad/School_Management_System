@extends('layouts.dashboard')
@section('title', 'Employees')
@php
    $blank = ['name' => '', 'designation' => '', 'department' => '', 'kind' => 'Teacher', 'gender' => 'Male', 'phone' => '', 'email' => '', 'address' => '', 'joining_date' => '', 'basic_salary' => '', 'status' => 'Active', 'show_public' => true];
    $reopen = $errors->any();
    $config = ['baseUrl' => route('admin.employees.index'), 'blank' => $blank, 'options' => [], 'reopen' => $reopen, 'reopenId' => $reopen ? (old('_editing_id') ?: null) : null, 'old' => $reopen ? old() : []];
@endphp
@section('content')
<div x-data="resourcePage(@js($config))">
    <x-page-head title="Employees" subtitle="Teachers and staff. Teachers get a sign-in automatically.">
        <button type="button" class="btn btn-primary" @click="add"><x-lucide-plus class="size-4" /> Add employee</button>
    </x-page-head>

    <form method="GET" class="mb-4 flex flex-wrap gap-2.5">
        <label class="relative min-w-56 flex-1 sm:max-w-sm"><span class="sr-only">Search</span>
            <x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-3" />
            <input class="input pl-10" type="search" name="q" value="{{ request('q') }}" placeholder="Name, ID or phone">
        </label>
        <select name="kind" class="input w-auto" aria-label="Type" onchange="this.form.submit()"><option value="">All employees</option>@foreach (['Teacher', 'Staff'] as $k)<option @selected(request('kind') === $k)>{{ $k }}</option>@endforeach</select>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Employee</th><th scope="col">ID</th><th scope="col">Department</th><th scope="col">Phone</th><th scope="col">Basic salary</th><th scope="col">Status</th><th scope="col" class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($employees as $e)
                        <tr>
                            <td><span class="flex items-center gap-3"><x-avatar :name="$e->name" :photo="$e->photo" class="size-9" /><span><span class="block font-semibold text-ink">{{ $e->name }}</span><span class="block text-xs text-ink-3">{{ $e->designation }}</span></span></span></td>
                            <td>{{ $e->employee_no }}</td><td>{{ $e->department ?: '-' }}</td><td>{{ $e->phone ?: '-' }}</td><td>{{ money($e->basic_salary) }}</td>
                            <td><span class="badge {{ status_badge($e->status) }}">{{ $e->status }}</span></td>
                            <td class="whitespace-nowrap text-right">
                                @if ($e->user_id)
                                    <form method="POST" action="{{ route('admin.employees.reset-password', $e) }}" class="inline">@csrf<button class="btn btn-sm" aria-label="Reset password for {{ $e->name }}" title="Reset password"><x-lucide-key-round class="size-4" /></button></form>
                                @endif
                                <button type="button" class="btn btn-sm" @click="edit(@js([...$e->only(array_keys($blank)), 'id' => $e->id, 'joining_date' => $e->joining_date?->format('Y-m-d')]))" aria-label="Edit {{ $e->name }}"><x-lucide-pencil class="size-4" /></button>
                                <button type="button" class="btn btn-sm text-rose-600 dark:text-rose-400" @click="deleting = {{ $e->id }}" aria-label="Delete {{ $e->name }}"><x-lucide-trash-2 class="size-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($employees->hasPages())<div class="border-t border-line px-5 py-3">{{ $employees->links() }}</div>@endif
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/50 p-4 sm:p-8" @keydown.escape.window="open = false" role="dialog" aria-modal="true">
        <form :action="action" method="POST" enctype="multipart/form-data" class="card w-full max-w-2xl" @click.outside="open = false">
            @csrf
            <template x-if="editingId"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_editing_id" :value="editingId ?? ''">
            <div class="card-head"><h2 class="card-title" x-text="editingId ? 'Edit employee' : 'Add employee'"></h2><button type="button" class="btn btn-sm" @click="open = false" aria-label="Close"><x-lucide-x class="size-4" /></button></div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                @foreach ([['name', 'Full name', 'text'], ['designation', 'Designation', 'text'], ['department', 'Department', 'text'], ['phone', 'Phone', 'tel'], ['email', 'Email', 'email'], ['address', 'Address', 'text'], ['joining_date', 'Joining date', 'date'], ['basic_salary', 'Basic salary', 'number']] as [$f, $label, $type])
                    <div><label class="field-label" for="e-{{ $f }}">{{ $label }} @if(in_array($f, ['name', 'basic_salary']))<span class="text-rose-600">*</span>@endif</label><input id="e-{{ $f }}" name="{{ $f }}" type="{{ $type }}" class="input" x-model="form.{{ $f }}" @required(in_array($f, ['name', 'basic_salary'])) @if($type === 'number') min="0" step="any" @endif>@error($f)<p class="field-error">{{ $message }}</p>@enderror</div>
                @endforeach
                @foreach ([['kind', 'Type', ['Teacher', 'Staff']], ['gender', 'Gender', ['Male', 'Female', 'Other']], ['status', 'Status', ['Active', 'Inactive']]] as [$f, $label, $choices])
                    <div><label class="field-label" for="e-{{ $f }}">{{ $label }}</label><select id="e-{{ $f }}" name="{{ $f }}" class="input" x-model="form.{{ $f }}">@foreach ($choices as $c)<option>{{ $c }}</option>@endforeach</select></div>
                @endforeach
                <div><label class="field-label" for="e-photo">Photo</label><input id="e-photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="input">@error('photo')<p class="field-error">{{ $message }}</p>@enderror</div>
                <label class="flex items-center gap-2.5 text-sm font-medium sm:col-span-2"><input type="checkbox" name="show_public" value="1" x-model="form.show_public" class="size-4 accent-indigo-600"> Show on the public website</label>
            </div>
            <div class="flex justify-end gap-2 border-t border-line px-5 py-4"><button type="button" class="btn" @click="open = false">Cancel</button><button class="btn btn-primary">Save</button></div>
        </form>
    </div>

    <div x-show="deleting" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="alertdialog" aria-modal="true">
        <form :action="`${@js($config['baseUrl'])}/${deleting}`" method="POST" class="card w-full max-w-sm p-6" @click.outside="deleting = null">
            @csrf @method('DELETE')
            <h2 class="text-lg font-bold">Delete this employee?</h2><p class="mt-1 text-sm text-ink-2">Their sign-in, attendance and payroll records will also be removed.</p>
            <div class="mt-5 flex justify-end gap-2"><button type="button" class="btn" @click="deleting = null">Cancel</button><button class="btn btn-danger">Delete</button></div>
        </form>
    </div>
</div>
@endsection
