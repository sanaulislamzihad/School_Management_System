@extends('layouts.public')
@section('title', 'Contact')
@section('content')
<x-web-page title="Contact Us">
    <div class="grid gap-8 md:grid-cols-[1fr_320px]">
        <div>
            @if (session('sent'))<p class="mb-4 rounded-lg bg-emerald-50 p-4 font-semibold text-emerald-900">Thank you. Your message has reached the school office.</p>@endif
            @php($f = 'w-full rounded-lg border border-slate-300 px-3 py-2')
            <form method="POST" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @foreach ([['name', 'Your name', 'text', true], ['phone', 'Phone', 'tel', false], ['email', 'Email', 'email', false], ['subject', 'Subject', 'text', false]] as [$n, $l, $t, $r])
                    <div><label for="{{ $n }}" class="mb-1 block text-sm font-semibold">{{ $l }} @if($r)<span class="text-rose-700">*</span>@endif</label><input id="{{ $n }}" type="{{ $t }}" name="{{ $n }}" value="{{ old($n) }}" class="{{ $f }}" @required($r)>@error($n)<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
                @endforeach
                <div class="sm:col-span-2"><label for="message" class="mb-1 block text-sm font-semibold">Message <span class="text-rose-700">*</span></label><textarea id="message" name="message" rows="5" class="{{ $f }}" required>{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
                <div><button class="rounded-full bg-brand px-6 py-2.5 font-semibold text-white">Send message</button></div>
            </form>
        </div>
        <aside class="self-start rounded-xl bg-indigo-50 p-5"><h2 class="font-bold text-navy">School office</h2><p class="mt-2 text-sm">{{ setting('address') }}</p><p class="mt-2 text-sm">{{ setting('phone') }}</p><p class="mt-2 text-sm">{{ setting('email') }}</p><p class="mt-3 text-sm text-slate-600">Open Saturday to Thursday, 9 AM to 2 PM.</p></aside>
    </div>
</x-web-page>
@endsection
