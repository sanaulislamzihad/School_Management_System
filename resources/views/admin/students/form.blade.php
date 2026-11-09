@extends('layouts.dashboard')
@php
    $editing = $student->exists;
    $title = $editing ? 'Edit student' : 'Add student';
    $classValue = (string) old('class_id', $student->class_id);
    $sectionValue = (string) old('section_id', $student->section_id);
    $currentSession = collect($sessions)->max('value');
@endphp
@section('title', $title)
@section('content')
<x-page-head :title="$title" subtitle="Fields marked * are required.">
    <a class="btn" href="{{ $editing ? route('admin.students.show', $student) : route('admin.students.index') }}"><x-lucide-arrow-left class="size-4" /> Back</a>
</x-page-head>

@if ($errors->any())
    <div class="mb-4 rounded-xl bg-rose-50 p-4 text-sm font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200" role="alert">Please correct the highlighted fields below.</div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.students.update', $student) : route('admin.students.store') }}" class="mx-auto max-w-4xl space-y-5">
    @csrf
    @if ($editing) @method('PUT') @endif

    <section class="card">
        <h2 class="card-head card-title">Personal information</h2>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="field-label" for="photo">Photo</label>
                <div class="flex items-center gap-4">
                    <x-avatar :name="$student->name ?: 'New Student'" :photo="$student->photo" class="size-16 text-base" />
                    <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="input">
                </div>
                @error('photo') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <x-field name="name" label="Full name" :value="$student->name" required />
            <div>
                <label class="field-label" for="gender">Gender <span class="text-rose-600">*</span></label>
                <select id="gender" name="gender" class="input">@foreach (['Male', 'Female', 'Other'] as $g)<option @selected(old('gender', $student->gender) === $g)>{{ $g }}</option>@endforeach</select>
            </div>
            <x-field name="dob" label="Date of birth" type="date" :value="$student->dob?->format('Y-m-d')" required />
            <div>
                <label class="field-label" for="blood_group">Blood group</label>
                <select id="blood_group" name="blood_group" class="input"><option value="">Unknown</option>@foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $b)<option @selected(old('blood_group', $student->blood_group) === $b)>{{ $b }}</option>@endforeach</select>
            </div>
            <x-field name="religion" label="Religion" :value="$student->religion" />
            <x-field name="birth_reg" label="Birth registration no." :value="$student->birth_reg" />
            <x-field name="phone" label="Student phone" type="tel" :value="$student->phone" />
            <x-field name="email" label="Email" type="email" :value="$student->email" />
            <x-field name="present_address" label="Present address" :value="$student->present_address" />
            <x-field name="permanent_address" label="Permanent address" :value="$student->permanent_address" />
            <x-field class="sm:col-span-2" name="medical_notes" label="Medical notes or allergies" type="textarea" :value="$student->medical_notes" />
        </div>
    </section>

    <section class="card" x-data="{ classId: @js($classValue), sections: @js($sections) }">
        <h2 class="card-head card-title">Academic information</h2>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <div>
                <label class="field-label" for="session_id">Session <span class="text-rose-600">*</span></label>
                <select id="session_id" name="session_id" class="input" required>@foreach ($sessions as $o)<option value="{{ $o['value'] }}" @selected(old('session_id', $student->session_id ?? \Illuminate\Support\Facades\DB::table('academic_sessions')->where('is_current', true)->value('id')) == $o['value'])>{{ $o['label'] }}</option>@endforeach</select>
            </div>
            <div>
                <label class="field-label" for="class_id">Class <span class="text-rose-600">*</span></label>
                <select id="class_id" name="class_id" class="input" x-model="classId" required><option value="">Select class</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}" @selected($classValue === (string) $o['value'])>{{ $o['label'] }}</option>@endforeach</select>
                @error('class_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label" for="section_id">Section <span class="text-rose-600">*</span></label>
                <select id="section_id" name="section_id" class="input" required><option value="">Select section</option><template x-for="o in sections.filter(s => String(s.parent) === classId)" :key="o.value"><option :value="o.value" x-text="o.label" :selected="String(o.value) === @js($sectionValue)"></option></template></select>
                @error('section_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label" for="shift_id">Shift</label>
                <select id="shift_id" name="shift_id" class="input"><option value="">None</option>@foreach ($shifts as $o)<option value="{{ $o['value'] }}" @selected(old('shift_id', $student->shift_id) == $o['value'])>{{ $o['label'] }}</option>@endforeach</select>
            </div>
            <x-field name="roll" label="Roll no." type="number" :value="$student->roll" />
            <x-field name="admission_date" label="Admission date" type="date" :value="$student->admission_date?->format('Y-m-d')" />
            <div>
                <label class="field-label" for="status">Status</label>
                <select id="status" name="status" class="input">@foreach (['Active', 'Inactive', 'Transferred', 'Passed Out'] as $s)<option @selected(old('status', $student->status) === $s)>{{ $s }}</option>@endforeach</select>
            </div>
        </div>
    </section>

    <section class="card">
        <h2 class="card-head card-title">Family and guardian</h2>
        <div class="grid gap-4 p-5 sm:grid-cols-3">
            <x-field name="father_name" label="Father's name" :value="$student->father_name" />
            <x-field name="father_phone" label="Father's phone" type="tel" :value="$student->father_phone" />
            <x-field name="father_occupation" label="Father's occupation" :value="$student->father_occupation" />
            <x-field name="mother_name" label="Mother's name" :value="$student->mother_name" />
            <x-field name="mother_phone" label="Mother's phone" type="tel" :value="$student->mother_phone" />
            <x-field name="mother_occupation" label="Mother's occupation" :value="$student->mother_occupation" />
            <x-field name="guardian_name" label="Guardian's name" :value="$student->guardian_name" />
            <x-field name="guardian_relation" label="Relation" :value="$student->guardian_relation" />
            <x-field name="guardian_phone" label="Guardian's phone" type="tel" :value="$student->guardian_phone" required />
        </div>
    </section>

    <section class="card">
        <h2 class="card-head card-title">Previous education</h2>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <x-field name="previous_school" label="Previous school" :value="$student->previous_school" />
            <x-field name="notes" label="Notes" :value="$student->notes" />
        </div>
    </section>

    <div class="flex justify-end gap-2">
        <a class="btn" href="{{ route('admin.students.index') }}">Cancel</a>
        <button class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add student' }}</button>
    </div>
</form>
@endsection
