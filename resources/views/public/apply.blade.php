@extends('layouts.public')
@section('title', 'Apply for Admission')
@section('content')
<x-web-page title="Apply for Admission">
    @if (session('reference'))
        <div class="mb-5 max-w-3xl rounded-xl bg-emerald-50 p-5 text-emerald-900"><p class="font-bold">Application received.</p><p class="mt-1">Your reference number is <strong>{{ session('reference') }}</strong>. Please keep it for future contact with the school office.</p></div>
    @endif
    @php($f = 'w-full rounded-lg border border-slate-300 px-3 py-2')
    <form method="POST" class="grid max-w-3xl gap-4 sm:grid-cols-2">
        @csrf
        @foreach ([['name', 'Applicant full name', 'text', true], ['guardian_name', 'Guardian name', 'text', true], ['phone', 'Guardian phone', 'tel', true], ['email', 'Email', 'email', false], ['previous_school', 'Previous school', 'text', false], ['address', 'Address', 'text', false]] as [$n, $l, $t, $r])
            <div><label for="{{ $n }}" class="mb-1 block text-sm font-semibold">{{ $l }} @if($r)<span class="text-rose-700">*</span>@endif</label><input id="{{ $n }}" type="{{ $t }}" name="{{ $n }}" value="{{ old($n) }}" class="{{ $f }}" @required($r)>@error($n)<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
        @endforeach
        <div><label for="gender" class="mb-1 block text-sm font-semibold">Gender <span class="text-rose-700">*</span></label><select id="gender" name="gender" class="{{ $f }}" required>@foreach (['Male', 'Female', 'Other'] as $g)<option @selected(old('gender') === $g)>{{ $g }}</option>@endforeach</select></div>
        <div><label for="dob" class="mb-1 block text-sm font-semibold">Date of birth <span class="text-rose-700">*</span></label><input id="dob" type="date" name="dob" value="{{ old('dob') }}" class="{{ $f }}" required>@error('dob')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
        <div><label for="class_id" class="mb-1 block text-sm font-semibold">Class applying for <span class="text-rose-700">*</span></label><select id="class_id" name="class_id" class="{{ $f }}" required><option value="">Select class</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}" @selected(old('class_id') == $o['value'])>{{ $o['label'] }}</option>@endforeach</select></div>
        <div class="sm:col-span-2"><button class="rounded-full bg-brand px-6 py-2.5 font-semibold text-white">Submit application</button></div>
    </form>
</x-web-page>
@endsection
