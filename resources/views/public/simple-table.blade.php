@extends('layouts.public')
@section('title', $title)
@section('content')
<x-web-page :title="$title">
    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-sm">
            <thead class="bg-indigo-50 text-left text-navy"><tr>@foreach ($headers as $h)<th scope="col" class="px-4 py-3 font-bold">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($rows as $row)<tr>@foreach ($row as $cell)<td class="px-4 py-3">{{ $cell }}</td>@endforeach</tr>
                @empty<tr><td class="px-4 py-6 text-slate-600" colspan="{{ count($headers) }}">Information will be added soon.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</x-web-page>
@endsection
