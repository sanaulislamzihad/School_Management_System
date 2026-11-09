@extends('layouts.public')
@section('title', 'Photo and Video Credits')
@section('content')
<x-web-page title="Photo and Video Credits">
    <p class="max-w-3xl text-slate-700">The demonstration photos and videos on this site are used under free licences from Wikimedia Commons. They do not show this school. Staff portraits are placeholder images from randomuser.me.</p>
    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-sm">
            <thead class="bg-indigo-50 text-left text-navy"><tr><th scope="col" class="px-4 py-3">Media</th><th scope="col" class="px-4 py-3">Author</th><th scope="col" class="px-4 py-3">Licence</th><th scope="col" class="px-4 py-3">Source</th></tr></thead>
            <tbody class="divide-y divide-slate-200">
                @foreach ([...$media['photos'], ...$media['videos']] as $item)
                    <tr><td class="px-4 py-2.5">{{ $item['caption'] ?? $item['title'] }}</td><td class="px-4 py-2.5">{{ $item['author'] ?: 'Unknown' }}</td><td class="px-4 py-2.5">{{ $item['license'] }}</td><td class="px-4 py-2.5"><a class="font-semibold text-navy underline" href="{{ $item['source'] }}" target="_blank" rel="noopener">Wikimedia Commons</a></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-web-page>
@endsection
