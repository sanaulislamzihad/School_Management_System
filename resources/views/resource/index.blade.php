@extends('layouts.dashboard')
@section('title', $def['title'])

@php
    use Illuminate\Support\Str;
    $reopen = $errors->any();
    $hasFiles = collect($def['fields'])->contains('type', 'image');
    $config = [
        'baseUrl' => $baseUrl,
        'blank' => $blank,
        'options' => $options,
        'reopen' => $reopen,
        'reopenId' => $reopen ? (old('_editing_id') ?: null) : null,
        'old' => $reopen ? old() : [],
    ];
@endphp

@section('content')
<div x-data="resourcePage(@js($config))">
    <x-page-head :title="$def['title']" :subtitle="$def['subtitle']">
        @if ($name === 'routines' || $name === 'teacher-subjects')
            <a class="btn" href="{{ route('admin.timetable.index') }}"><x-lucide-calendar-days class="size-4" /> View timetable</a>
        @endif
        @empty($def['noCreate'])
            <button type="button" class="btn btn-primary" @click="add"><x-lucide-plus class="size-4" /> Add {{ $def['singular'] }}</button>
        @endempty
    </x-page-head>

    @isset($def['tabs'])
        <nav class="mb-4 flex flex-wrap gap-1.5" aria-label="Sections">
            @foreach ($def['tabs'] as $key => $label)
                <a href="{{ staff_route('resource.index', ['resource' => $key]) }}" @class(['btn btn-sm', 'btn-primary' => $key === $name]) @if($key === $name) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    @endisset

    @if (! empty($def['search']) || $filters)
        <form class="mb-4 flex flex-wrap gap-2.5" method="GET">
            @if (! empty($def['search']))
                <label class="relative min-w-56 flex-1 sm:max-w-sm">
                    <span class="sr-only">Search</span>
                    <x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-3" />
                    <input class="input pl-10" type="search" name="q" value="{{ request('q') }}" placeholder="Search...">
                </label>
            @endif
            @foreach ($filters as $filter)
                <select name="{{ $filter['name'] }}" class="input w-auto" aria-label="{{ $filter['label'] }}" onchange="this.form.submit()">
                    <option value="">{{ $filter['label'] }}</option>
                    @foreach ($filter['choices'] as $choice)
                        <option value="{{ $choice['value'] }}" @selected((string) request($filter['name']) === (string) $choice['value'])>{{ $choice['label'] }}</option>
                    @endforeach
                </select>
            @endforeach
            <noscript><button class="btn">Apply</button></noscript>
        </form>
    @endif

    <div class="card overflow-hidden">
        @if ($rows->isEmpty())
            <x-empty :title="'No '.Str::plural($def['singular']).' found'" text="Try a different search, or add the first one." />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            @foreach ($def['columns'] as $column)
                                <th scope="col">{{ $column['label'] }}</th>
                            @endforeach
                            <th scope="col" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($def['columns'] as $i => $column)
                                    @php($value = $row->{$column['key']})
                                    <td @class(['font-semibold text-ink' => $i === 0])>
                                        @switch($column['type'] ?? 'text')
                                            @case('date') {{ nice_date($value) }} @break
                                            @case('money') {{ money($value) }} @break
                                            @case('bool')
                                                <span class="badge {{ $value ? 'badge-green' : 'badge-gray' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                                @break
                                            @case('badge') <span class="badge {{ status_badge($value) }}">{{ Str::headline((string) $value) }}</span> @break
                                            @case('image')
                                                @if ($value) <img src="{{ asset($value) }}" alt="" class="h-10 w-14 rounded-lg object-cover"> @else <span class="text-ink-3">None</span> @endif
                                                @break
                                            @default {{ Str::limit((string) $value, 70) ?: '-' }}
                                        @endswitch
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap text-right">
                                    @isset($def['rowLink'])
                                        <a class="btn btn-sm" href="{{ staff_route($def['rowLink']['route'], ['classTest' => $row->id]) }}">{{ $def['rowLink']['label'] }}</a>
                                    @endisset
                                    <button type="button" class="btn btn-sm" @click="edit(@js($row))" aria-label="Edit"><x-lucide-pencil class="size-4" /></button>
                                    <button type="button" class="btn btn-sm text-rose-600 dark:text-rose-400" @click="deleting = {{ $row->id }}" aria-label="Delete"><x-lucide-trash-2 class="size-4" /></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($rows->hasPages())
                <div class="border-t border-line px-5 py-3">{{ $rows->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Add / edit dialog --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/50 p-4 sm:p-8" @keydown.escape.window="open = false" role="dialog" aria-modal="true">
        <form :action="action" method="POST" @if($hasFiles) enctype="multipart/form-data" @endif class="card w-full max-w-2xl" @click.outside="open = false">
            @csrf
            <template x-if="editingId"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_editing_id" :value="editingId ?? ''">
            <div class="card-head">
                <h2 class="card-title" x-text="(editingId ? 'Edit ' : 'Add ') + @js($def['singular'])"></h2>
                <button type="button" class="btn btn-sm" @click="open = false" aria-label="Close"><x-lucide-x class="size-4" /></button>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                @foreach ($def['fields'] as $field)
                    @php($f = $field['name'])
                    <div @class(['sm:col-span-2' => ! empty($field['wide']) || $field['type'] === 'textarea'])>
                        @if ($field['type'] === 'checkbox')
                            <label class="mt-6 flex items-center gap-2.5 text-sm font-medium">
                                <input type="checkbox" name="{{ $f }}" value="1" x-model="form.{{ $f }}" class="size-4 accent-indigo-600"> {{ $field['label'] }}
                            </label>
                        @else
                            <label class="field-label" for="f-{{ $f }}">{{ $field['label'] }} @if (! empty($field['required']) && $field['type'] !== 'image')<span class="text-rose-600">*</span>@endif</label>
                            @switch($field['type'])
                                @case('textarea')
                                    <textarea id="f-{{ $f }}" name="{{ $f }}" rows="4" class="input" x-model="form.{{ $f }}"></textarea>
                                    @break
                                @case('select')
                                    <select id="f-{{ $f }}" name="{{ $f }}" class="input" x-model="form.{{ $f }}" @if(! empty($field['required'])) required @endif>
                                        <option value="">Select...</option>
                                        <template x-for="o in choices(@js($field))" :key="o.value">
                                            <option :value="o.value" x-text="o.label" :selected="String(o.value) === String(form.{{ $f }})"></option>
                                        </template>
                                    </select>
                                    @break
                                @case('image')
                                    <input id="f-{{ $f }}" name="{{ $f }}" type="file" accept="image/png,image/jpeg,image/webp" class="input" :required="@js(! empty($field['required'])) && !editingId">
                                    <p class="mt-1 text-xs text-ink-3">PNG, JPG or WebP, up to 4 MB.<span x-show="editingId"> Leave empty to keep the current image.</span></p>
                                    @break
                                @default
                                    <input id="f-{{ $f }}" name="{{ $f }}" class="input" x-model="form.{{ $f }}" type="{{ $field['type'] === 'url' ? 'text' : $field['type'] }}" @if($field['type'] === 'number') step="any" min="0" @endif @if(! empty($field['required'])) required @endif>
                            @endswitch
                            @error($f) <p class="field-error">{{ $message }}</p> @enderror
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 border-t border-line px-5 py-4">
                <button type="button" class="btn" @click="open = false">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>

    {{-- Delete confirmation --}}
    <div x-show="deleting" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="alertdialog" aria-modal="true">
        <form :action="`${@js($baseUrl)}/${deleting}`" method="POST" class="card w-full max-w-sm p-6" @click.outside="deleting = null">
            @csrf @method('DELETE')
            <h2 class="text-lg font-bold">Delete this {{ $def['singular'] }}?</h2>
            <p class="mt-1 text-sm text-ink-2">This cannot be undone.</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn" @click="deleting = null">Cancel</button>
                <button class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection
