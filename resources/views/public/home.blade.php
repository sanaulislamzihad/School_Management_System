@extends('layouts.public')

@php
    use Illuminate\Support\Str;
    $slides = [['classroom.jpg', 'Where every student is known by name'], ['campus.jpg', 'A green and safe campus'], ['assembly.jpg', 'Discipline, unity and school spirit']];
@endphp

@section('content')
<div class="bg-slate-100">
    <div class="mx-auto flex max-w-6xl items-center px-4 py-2.5">
        <a href="{{ route('notices.index') }}" class="z-10 rounded-l-md bg-leaf px-4 py-1.5 text-sm font-bold text-white">Notice</a>
        <div class="flex-1 overflow-hidden rounded-r-md bg-white py-1.5">
            <p class="ticker-track whitespace-nowrap text-sm font-medium text-navy">{{ setting('ticker') }}</p>
        </div>
    </div>
</div>

<section class="bg-gradient-to-b from-slate-100 to-white pb-6">
    <div class="mx-auto grid max-w-6xl gap-5 px-4 pt-4 lg:grid-cols-[1fr_320px]">
        <div x-data="{ i: 0, n: {{ count($slides) }} }" x-init="if (!matchMedia('(prefers-reduced-motion: reduce)').matches) setInterval(() => i = (i + 1) % n, 5500)" class="relative h-72 overflow-hidden rounded-xl shadow-md sm:h-[440px]">
            @foreach ($slides as $index => [$file, $caption])
                <figure x-show="i === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0" @if($index) x-cloak @endif>
                    <img src="{{ asset('images/school/'.$file) }}" alt="{{ $caption }}" class="size-full object-cover">
                    <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-6 pt-16 text-xl font-bold text-white sm:text-2xl">{{ $caption }}</figcaption>
                </figure>
            @endforeach
            <div class="absolute bottom-4 right-5 flex gap-1.5">
                @foreach ($slides as $index => $slide)
                    <button type="button" @click="i = {{ $index }}" class="size-2.5 rounded-full" :class="i === {{ $index }} ? 'bg-white' : 'bg-white/50'" aria-label="Show slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        </div>

        <aside class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between bg-navy px-4 py-3 text-white">
                <h2 class="flex items-center gap-2 text-lg font-bold"><x-lucide-megaphone class="size-5" /> Notices</h2>
                <a href="{{ route('notices.index') }}" class="rounded-full border border-white/40 px-3 py-0.5 text-[13px] hover:bg-white/10">View all</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach ($notices as $notice)
                    <li><a href="{{ route('notices.show', $notice->id) }}" class="flex gap-3 px-4 py-2.5 hover:bg-sky-50">
                        <span class="w-13 shrink-0 overflow-hidden rounded-md border border-slate-200 text-center">
                            <span class="block bg-navy py-0.5 text-sm font-bold text-white">{{ \Carbon\Carbon::parse($notice->date)->format('d') }}</span>
                            <span class="block py-0.5 text-[11px] text-slate-600">{{ \Carbon\Carbon::parse($notice->date)->format('M') }}</span>
                        </span>
                        <span class="text-sm font-medium leading-snug text-slate-800">{{ $notice->title }}</span>
                    </a></li>
                @endforeach
            </ul>
        </aside>
    </div>
</section>

<section class="mx-auto grid max-w-6xl gap-5 px-4 md:grid-cols-2">
    @foreach ($messages as $message)
        <article class="flex gap-4 rounded-xl border-2 border-leaf/70 bg-white p-4">
            <div class="w-32 shrink-0">
                <img src="{{ asset($message->photo) }}" alt="{{ $message->name }}" class="h-36 w-full rounded-md object-cover">
                <p class="mt-1.5 rounded-md bg-leaf px-1.5 py-1 text-center text-xs font-bold text-white">{{ $message->designation }}</p>
            </div>
            <div>
                <h2 class="font-bold text-navy">{{ $message->heading }}</h2>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-700">{{ Str::limit($message->body, 240) }}</p>
                <a href="{{ route('message', $message->slug) }}" class="mt-1.5 inline-block text-sm font-bold text-leaf-dark hover:underline">Read more</a>
            </div>
        </article>
    @endforeach
</section>

<section class="mx-auto mt-8 grid max-w-6xl grid-cols-2 gap-3 px-4 lg:grid-cols-4">
    @foreach ([['Students', $stats['students'], 'graduation-cap'], ['Teachers', $stats['teachers'], 'users'], ['Classes', $stats['classes'], 'school'], ['Years of service', $stats['years'], 'award']] as [$label, $value, $icon])
        <div class="flex items-center gap-3 rounded-xl bg-navy p-4 text-white">
            <x-dynamic-component :component="'lucide-'.$icon" class="size-8 text-sky-200" />
            <div><p class="text-2xl font-extrabold">{{ $value }}</p><p class="text-[13px] text-sky-100">{{ $label }}</p></div>
        </div>
    @endforeach
</section>

<section class="mt-8 bg-slate-100 py-8">
    <div class="mx-auto grid max-w-6xl gap-5 px-4 lg:grid-cols-[260px_1fr_260px]">
        <div class="space-y-5">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <h2 class="bg-leaf px-4 py-3 text-lg font-bold text-white">Quick access</h2>
                <div class="grid gap-2 p-3 text-sm font-semibold text-navy">
                    <a class="rounded-lg border border-slate-200 px-3 py-2.5 hover:bg-sky-50" href="{{ route('result') }}">Check exam result</a>
                    <a class="rounded-lg border border-slate-200 px-3 py-2.5 hover:bg-sky-50" href="{{ route('apply') }}">Apply for admission</a>
                    <a class="rounded-lg border border-slate-200 px-3 py-2.5 hover:bg-sky-50" href="{{ route('routine') }}">Class routine</a>
                    <a class="rounded-lg border border-slate-200 px-3 py-2.5 hover:bg-sky-50" href="{{ route('student.login') }}">Student portal</a>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <h2 class="bg-leaf px-4 py-3 text-lg font-bold text-white">Useful links</h2>
                <ul class="space-y-1 p-3 text-sm">
                    @foreach ($links as $link)
                        <li><a href="{{ $link->url }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-slate-700 hover:bg-sky-50 hover:text-navy"><x-lucide-chevron-right class="size-3.5 text-leaf-dark" /> {{ $link->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="space-y-5">
            <article class="overflow-hidden rounded-xl bg-white shadow-sm">
                <h2 class="bg-leaf px-4 py-3 text-center text-lg font-bold text-white">About {{ setting('school_name') }}</h2>
                <div class="p-4">
                    <img src="{{ asset($about->image) }}" alt="School building" class="h-52 w-full rounded-lg object-cover">
                    <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ Str::limit($about->body, 340) }}</p>
                    <a href="{{ route('page', 'history') }}" class="mt-2 inline-block text-sm font-bold text-leaf-dark hover:underline">Read more</a>
                </div>
            </article>
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <h2 class="bg-leaf px-4 py-3 text-lg font-bold text-white">Upcoming events</h2>
                <ul class="divide-y divide-slate-100">
                    @forelse ($events as $event)
                        <li class="p-4"><p class="font-bold text-navy">{{ $event->title }}</p><p class="mt-0.5 text-[13px] text-slate-600">{{ nice_date($event->date) }} · {{ $event->location }}</p></li>
                    @empty
                        <li class="p-4 text-sm text-slate-600">New events will be announced soon.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <aside class="self-start overflow-hidden rounded-xl bg-white shadow-sm">
            <h2 class="bg-leaf px-4 py-3 text-lg font-bold text-white">Top achievers</h2>
            <ul class="max-h-[480px] space-y-2 overflow-y-auto p-3">
                @foreach ($achievers as $achiever)
                    <li class="flex items-center gap-3 rounded-lg border border-slate-200 p-2.5">
                        <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-full border-2 border-leaf bg-emerald-50 text-sm font-bold text-leaf-dark">{{ initials($achiever->name) }}</span>
                        <span><span class="block text-sm font-bold text-navy">{{ $achiever->name }}</span><span class="block text-xs text-slate-600">{{ $achiever->exam }} {{ $achiever->year }} · GPA {{ $achiever->gpa }}</span></span>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</section>

<section class="mx-auto mt-10 max-w-6xl px-4">
    <div class="flex items-end justify-between">
        <h2 class="text-2xl font-extrabold text-navy">Our Teachers</h2>
        <a class="text-sm font-bold text-leaf-dark hover:underline" href="{{ route('teachers') }}">Meet all teachers</a>
    </div>
    <div class="mt-4 flex snap-x gap-4 overflow-x-auto pb-3">
        @foreach ($teachers as $teacher)
            <div class="w-48 shrink-0 snap-start overflow-hidden rounded-xl border border-slate-200 bg-white text-center shadow-sm">
                <img src="{{ asset($teacher->photo) }}" alt="{{ $teacher->name }}" class="h-44 w-full object-cover">
                <div class="p-3"><p class="font-bold text-navy">{{ $teacher->name }}</p><p class="text-[13px] text-slate-600">{{ $teacher->designation }}</p></div>
            </div>
        @endforeach
    </div>
</section>

<section class="mx-auto mt-10 max-w-6xl px-4">
    <div class="flex items-end justify-between">
        <h2 class="text-2xl font-extrabold text-navy">Photo Gallery</h2>
        <a class="text-sm font-bold text-leaf-dark hover:underline" href="{{ route('gallery.photos') }}">View all photos</a>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach ($photos as $photo)
            <figure class="group relative h-40 overflow-hidden rounded-xl">
                <img src="{{ asset($photo->image_url) }}" alt="{{ $photo->title }}" loading="lazy" class="size-full object-cover transition-transform group-hover:scale-105">
                <figcaption class="absolute inset-x-0 bottom-0 bg-black/60 px-2.5 py-1.5 text-[13px] font-medium text-white">{{ $photo->title }}</figcaption>
            </figure>
        @endforeach
    </div>
</section>

<section class="mx-auto mt-10 max-w-6xl px-4">
    <div class="rounded-xl bg-slate-950 p-6">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-extrabold text-white">Video Gallery</h2>
            <a class="text-sm font-bold text-sky-300 hover:underline" href="{{ route('gallery.videos') }}">View all videos</a>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($videos as $video)
                <x-video-card :video="$video" />
            @endforeach
        </div>
    </div>
</section>
@endsection
