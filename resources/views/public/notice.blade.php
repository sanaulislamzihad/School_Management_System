@extends('layouts.public')
@section('title', $notice->title)
@section('content')
<x-web-page :title="$notice->title">
    <article class="max-w-3xl">
        <p class="text-sm font-semibold text-slate-600">{{ nice_date($notice->date) }} · {{ $notice->category }}</p>
        <div class="web-body mt-4 leading-relaxed">@foreach (preg_split('/\n\s*\n/', (string) $notice->body) as $p)<p>{{ $p }}</p>@endforeach</div>
        <a href="{{ route('notices.index') }}" class="mt-6 inline-block font-bold text-leaf-dark hover:underline">Back to all notices</a>
    </article>
</x-web-page>
@endsection
