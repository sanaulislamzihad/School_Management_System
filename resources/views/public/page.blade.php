@extends('layouts.public')
@section('title', $title)
@section('content')
<x-web-page :title="$title">
    <div class="grid gap-8 lg:grid-cols-[1fr_280px]">
        <article>
            @if ($image) <img src="{{ asset($image) }}" alt="" class="mb-5 h-80 w-full rounded-xl object-cover"> @endif
            <div class="web-body leading-relaxed text-slate-700">@foreach (preg_split('/\n\s*\n/', (string) $body) as $p)<p>{{ $p }}</p>@endforeach</div>
        </article>
        <aside class="self-start overflow-hidden rounded-xl border border-slate-200">
            <h2 class="bg-leaf px-4 py-3 font-bold text-white">In this section</h2>
            <ul class="p-2">@foreach ($related as $item)<li><a href="{{ route('page', $item->slug) }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-indigo-50">{{ $item->title }}</a></li>@endforeach</ul>
        </aside>
    </div>
</x-web-page>
@endsection
