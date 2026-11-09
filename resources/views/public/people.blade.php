@extends('layouts.public')
@section('title', $title)
@section('content')
<x-web-page :title="$title">
    @if ($people->isEmpty())
        <p class="text-slate-600">Information will be added soon.</p>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($people as $person)
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white text-center shadow-sm">
                    @if ($person->photo)
                        <img src="{{ asset($person->photo) }}" alt="{{ $person->name }}" loading="lazy" class="h-52 w-full object-cover">
                    @else
                        <span class="flex h-52 items-center justify-center bg-indigo-50 text-4xl font-extrabold text-navy">{{ initials($person->name) }}</span>
                    @endif
                    <div class="p-3"><p class="font-bold text-navy">{{ $person->name }}</p><p class="text-[13px] text-slate-600">{{ $person->designation }}</p></div>
                </div>
            @endforeach
        </div>
    @endif
</x-web-page>
@endsection
