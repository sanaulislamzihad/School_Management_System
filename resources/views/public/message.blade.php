@extends('layouts.public')
@section('title', $message->heading)
@section('content')
<x-web-page :title="$message->heading">
    <div class="grid gap-7 md:grid-cols-[240px_1fr]">
        <div>
            <img src="{{ asset($message->photo) }}" alt="{{ $message->name }}" class="h-64 w-full rounded-xl object-cover">
            <p class="mt-3 font-bold text-navy">{{ $message->name }}</p><p class="text-sm text-slate-600">{{ $message->designation }}</p>
        </div>
        <div class="web-body leading-relaxed text-slate-700">@foreach (preg_split('/\n\s*\n/', (string) $message->body) as $p)<p>{{ $p }}</p>@endforeach</div>
    </div>
</x-web-page>
@endsection
