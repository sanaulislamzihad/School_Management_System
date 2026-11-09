@extends('layouts.dashboard')
@section('title', 'Settings')
@section('content')
<x-page-head title="School settings" subtitle="These details appear across the website and dashboards." />
<form method="POST" action="{{ route('admin.settings.update') }}" class="card max-w-3xl">
    @csrf @method('PUT')
    <div class="grid gap-4 p-5 sm:grid-cols-2">
        @foreach ($fields as $key => [$label, $rule])
            <x-field :name="$key" :label="$label" :value="$values[$key] ?? ''" :required="$rule === 'required'" @class(['sm:col-span-2' => in_array($key, ['address', 'ticker'])]) />
        @endforeach
    </div>
    <div class="flex justify-end border-t border-line px-5 py-4"><button class="btn btn-primary">Save settings</button></div>
</form>
@endsection
