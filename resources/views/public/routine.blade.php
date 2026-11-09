@extends('layouts.public')
@section('title', 'Class Routine')
@section('content')
<x-web-page title="Class Routine">
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3" x-data="{ c: @js((string) request('class_id')), sections: @js($sections) }">
        <div><label class="mb-1 block text-sm font-semibold" for="c">Class</label><select id="c" name="class_id" x-model="c" class="rounded-lg border border-slate-300 px-3 py-2" required><option value="">Select class</option>@foreach ($classes as $o)<option value="{{ $o['value'] }}" @selected((string) request('class_id') === (string) $o['value'])>{{ $o['label'] }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-sm font-semibold" for="s">Section</label><select id="s" name="section_id" class="rounded-lg border border-slate-300 px-3 py-2" required><option value="">Select section</option><template x-for="o in sections.filter(x => String(x.parent) === c)" :key="o.value"><option :value="o.value" x-text="o.label" :selected="String(o.value) === @js((string) request('section_id'))"></option></template></select></div>
        <button class="rounded-full bg-brand px-5 py-2 font-semibold text-white">Show routine</button>
    </form>
    @if ($periods->isNotEmpty())
        <div class="grid gap-4 md:grid-cols-2">
            @foreach (\App\Http\Controllers\TimetableController::DAYS as $day)
                @isset($periods[$day])
                    <section class="rounded-xl border border-slate-200 p-4"><h2 class="font-bold text-navy">{{ $day }}</h2><ul class="mt-2 divide-y divide-slate-100 text-sm">@foreach ($periods[$day] as $p)<li class="flex justify-between gap-3 py-2"><span>{{ nice_time($p->start_time) }} to {{ nice_time($p->end_time) }}</span><span class="font-semibold">{{ $p->subject_name }}</span></li>@endforeach</ul></section>
                @endisset
            @endforeach
        </div>
    @elseif (request('section_id'))
        <p class="text-slate-600">No routine has been published for this section yet.</p>
    @endif
</x-web-page>
@endsection
