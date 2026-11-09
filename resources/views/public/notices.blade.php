@extends('layouts.public')
@section('title', 'Notices')
@section('content')
<x-web-page title="Notice Board">
    <div class="divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200">
        @foreach ($notices as $notice)
            <a href="{{ route('notices.show', $notice->id) }}" class="flex items-center gap-4 p-4 hover:bg-sky-50">
                <span class="w-16 shrink-0 text-sm font-bold text-navy">{{ \Carbon\Carbon::parse($notice->date)->format('M j') }}</span>
                <span class="flex-1 font-semibold">{{ $notice->title }}</span>
                <span class="hidden rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-navy sm:inline">{{ $notice->category }}</span>
            </a>
        @endforeach
    </div>
    <div class="mt-5">{{ $notices->links() }}</div>
</x-web-page>
@endsection
