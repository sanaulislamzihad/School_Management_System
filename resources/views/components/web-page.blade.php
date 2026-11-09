@props(['title'])
<div class="bg-navy py-8 text-white">
    <div class="mx-auto max-w-6xl px-4">
        <h1 class="text-3xl font-extrabold tracking-tight">{{ $title }}</h1>
        <nav class="mt-1 text-sm text-indigo-100" aria-label="Breadcrumb"><a class="hover:underline" href="{{ route('home') }}">Home</a> / {{ $title }}</nav>
    </div>
</div>
<div class="mx-auto max-w-6xl px-4 py-8">{{ $slot }}</div>
