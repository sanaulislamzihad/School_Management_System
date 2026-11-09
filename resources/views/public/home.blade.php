@extends('layouts.public')

@php
    use Illuminate\Support\Str;
    $slides = [['classroom.jpg', 'Where every student is known by name'], ['campus.jpg', 'A green and safe campus'], ['assembly.jpg', 'Discipline, unity and school spirit']];
    $panel = 'rounded-3xl border border-slate-200 bg-white';
    $more = 'inline-flex items-center gap-1 text-sm font-bold text-brand-dark hover:underline';
@endphp

@section('content')
<section class="bg-indigo-50 pb-12">
    <div class="mx-auto max-w-6xl px-4 pt-4">
        <div class="flex items-center gap-3 overflow-hidden rounded-full bg-white p-1.5">
            <a href="{{ route('notices.index') }}" class="z-10 shrink-0 rounded-full bg-sun px-4 py-1 text-sm font-bold text-navy">Notice</a>
            <div class="flex-1 overflow-hidden"><p class="ticker-track whitespace-nowrap text-sm font-medium text-navy">{{ setting('ticker') }}</p></div>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_340px]">
            <div x-data="{ i: 0, n: {{ count($slides) }} }" x-init="if (!matchMedia('(prefers-reduced-motion: reduce)').matches) setInterval(() => i = (i + 1) % n, 5500)" class="relative h-96 overflow-hidden rounded-3xl sm:h-[470px]">
                @foreach ($slides as $index => [$file, $caption])
                    <div x-show="i === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0" @if($index) x-cloak @endif>
                        <img src="{{ asset('images/school/'.$file) }}" alt="" class="size-full object-cover">
                        <div class="absolute inset-x-4 bottom-4 rounded-2xl bg-white p-5 sm:right-auto sm:max-w-md">
                            <p class="text-xl font-extrabold leading-snug tracking-tight text-navy sm:text-2xl">{{ $caption }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('apply') }}" class="rounded-full bg-brand px-5 py-2 text-sm font-bold text-white hover:bg-brand-dark">Apply for admission</a>
                                <a href="{{ route('result') }}" class="rounded-full bg-indigo-100 px-5 py-2 text-sm font-bold text-brand-dark hover:bg-indigo-200">Check result</a>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="absolute right-5 top-5 flex gap-1.5 rounded-full bg-white px-2.5 py-2">
                    @foreach ($slides as $index => $slide)
                        <button type="button" @click="i = {{ $index }}" class="size-2.5 rounded-full" :class="i === {{ $index }} ? 'bg-brand' : 'bg-slate-300'" aria-label="Show slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            </div>

            <aside class="rounded-3xl bg-white p-5">
                <div class="flex items-center justify-between">
                    <x-web-panel-title icon="megaphone" title="Notices" />
                    <a href="{{ route('notices.index') }}" class="{{ $more }}">View all</a>
                </div>
                <ul class="mt-3 space-y-1">
                    @foreach ($notices as $notice)
                        <li><a href="{{ route('notices.show', $notice->id) }}" class="flex items-center gap-3 rounded-2xl p-2 hover:bg-indigo-50">
                            <span class="w-12 shrink-0 rounded-xl bg-indigo-100 py-1 text-center text-brand-dark">
                                <span class="block text-base font-extrabold leading-tight">{{ \Carbon\Carbon::parse($notice->date)->format('d') }}</span>
                                <span class="block text-[11px] font-semibold">{{ \Carbon\Carbon::parse($notice->date)->format('M') }}</span>
                            </span>
                            <span class="text-sm font-semibold leading-snug text-slate-800">{{ $notice->title }}</span>
                        </a></li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </div>
</section>

<section class="mx-auto -mt-6 grid max-w-6xl grid-cols-2 gap-3 px-4 lg:grid-cols-4">
    @foreach ([['Students', $stats['students'], 'graduation-cap', 'bg-brand-dark'], ['Teachers', $stats['teachers'], 'users', 'bg-teal-700'], ['Classes', $stats['classes'], 'school', 'bg-orange-700'], ['Years of service', $stats['years'], 'award', 'bg-rose-700']] as [$label, $value, $icon, $color])
        <div class="{{ $color }} flex items-center gap-3.5 rounded-2xl p-4 text-white">
            <span class="inline-flex size-12 items-center justify-center rounded-xl bg-white/15"><x-dynamic-component :component="'lucide-'.$icon" class="size-6" /></span>
            <div><p class="text-2xl font-extrabold leading-tight">{{ $value }}</p><p class="text-[13px]">{{ $label }}</p></div>
        </div>
    @endforeach
</section>

<section class="mx-auto mt-10 grid max-w-6xl gap-5 px-4 md:grid-cols-2">
    @foreach ($messages as $message)
        <article class="{{ $panel }} flex gap-5 p-5">
            <img src="{{ asset($message->photo) }}" alt="{{ $message->name }}" class="h-44 w-32 shrink-0 rounded-2xl object-cover">
            <div>
                <h2 class="text-lg font-bold leading-snug text-navy">{{ $message->heading }}</h2>
                <p class="mt-0.5 text-[13px] font-semibold text-teal-700">{{ $message->name }} · {{ $message->designation }}</p>
                <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ Str::limit($message->body, 200) }}</p>
                <a href="{{ route('message', $message->slug) }}" class="{{ $more }} mt-2">Read more <x-lucide-arrow-right class="size-4" /></a>
            </div>
        </article>
    @endforeach
</section>

<section class="mt-10 bg-slate-100 py-10">
    <div class="mx-auto grid max-w-6xl gap-5 px-4 lg:grid-cols-[260px_1fr_270px]">
        <div class="space-y-5">
            <div class="{{ $panel }} p-5">
                <x-web-panel-title icon="zap" title="Quick access" tone="amber" />
                <div class="mt-3 grid gap-1 text-sm font-semibold text-navy">
                    @foreach ([['Check exam result', route('result'), 'award'], ['Apply for admission', route('apply'), 'clipboard-list'], ['Class routine', route('routine'), 'calendar-days'], ['Student portal', route('student.login'), 'graduation-cap']] as [$label, $url, $icon])
                        <a class="flex items-center gap-2.5 rounded-xl px-2.5 py-2.5 hover:bg-indigo-50" href="{{ $url }}"><x-dynamic-component :component="'lucide-'.$icon" class="size-4 text-brand-dark" /> {{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div class="{{ $panel }} p-5">
                <x-web-panel-title icon="link" title="Useful links" tone="teal" />
                <ul class="mt-3 space-y-0.5 text-sm">
                    @foreach ($links as $link)
                        <li><a href="{{ $link->url }}" target="_blank" rel="noopener" class="block rounded-xl px-2.5 py-2 text-slate-700 hover:bg-indigo-50 hover:text-navy">{{ $link->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="space-y-5">
            <article class="{{ $panel }} p-5">
                <x-web-panel-title icon="school" :title="'About '.setting('school_name')" />
                <img src="{{ asset($about->image) }}" alt="School building" class="mt-4 h-56 w-full rounded-2xl object-cover">
                <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ Str::limit($about->body, 340) }}</p>
                <a href="{{ route('page', 'history') }}" class="{{ $more }} mt-2">Read more <x-lucide-arrow-right class="size-4" /></a>
            </article>
            <div class="{{ $panel }} p-5">
                <x-web-panel-title icon="calendar-days" title="Upcoming events" tone="rose" />
                <ul class="mt-3 space-y-1">
                    @forelse ($events as $event)
                        <li class="flex items-center gap-3 rounded-2xl p-2">
                            <span class="w-12 shrink-0 rounded-xl bg-rose-100 py-1 text-center text-rose-700">
                                <span class="block text-base font-extrabold leading-tight">{{ \Carbon\Carbon::parse($event->date)->format('d') }}</span>
                                <span class="block text-[11px] font-semibold">{{ \Carbon\Carbon::parse($event->date)->format('M') }}</span>
                            </span>
                            <span><span class="block font-bold text-navy">{{ $event->title }}</span><span class="block text-[13px] text-slate-600">{{ $event->location }}</span></span>
                        </li>
                    @empty
                        <li class="p-2 text-sm text-slate-600">New events will be announced soon.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <aside class="{{ $panel }} self-start p-5">
            <x-web-panel-title icon="trophy" title="Top achievers" tone="violet" />
            <ul class="mt-3 space-y-2">
                @foreach ($achievers as $achiever)
                    <li class="flex items-center gap-3 rounded-2xl bg-slate-50 p-2.5">
                        <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-700">{{ initials($achiever->name) }}</span>
                        <span><span class="block text-sm font-bold text-navy">{{ $achiever->name }}</span><span class="block text-xs text-slate-600">{{ $achiever->exam }} {{ $achiever->year }} · GPA {{ $achiever->gpa }}</span></span>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</section>

<section class="mx-auto mt-12 max-w-6xl px-4">
    <div class="flex items-end justify-between">
        <h2 class="text-2xl font-extrabold tracking-tight text-navy">Our Teachers</h2>
        <a class="{{ $more }}" href="{{ route('teachers') }}">Meet all teachers <x-lucide-arrow-right class="size-4" /></a>
    </div>
    <div class="mt-4 flex snap-x gap-4 overflow-x-auto pb-3">
        @foreach ($teachers as $teacher)
            <div class="{{ $panel }} w-52 shrink-0 snap-start overflow-hidden text-center">
                <img src="{{ asset($teacher->photo) }}" alt="{{ $teacher->name }}" class="h-52 w-full object-cover">
                <div class="p-3.5"><p class="font-bold text-navy">{{ $teacher->name }}</p><p class="text-[13px] font-medium text-teal-700">{{ $teacher->designation }}</p></div>
            </div>
        @endforeach
    </div>
</section>

<section class="mx-auto mt-10 max-w-6xl px-4">
    <div class="flex items-end justify-between">
        <h2 class="text-2xl font-extrabold tracking-tight text-navy">Photo Gallery</h2>
        <a class="{{ $more }}" href="{{ route('gallery.photos') }}">View all photos <x-lucide-arrow-right class="size-4" /></a>
    </div>
    <div class="mt-4 grid auto-rows-[190px] grid-cols-2 gap-3 md:grid-cols-4">
        @foreach ($photos->take(5) as $photo)
            <figure @class(['group relative overflow-hidden rounded-3xl', 'col-span-2 row-span-2' => $loop->first])>
                <img src="{{ asset($photo->image_url) }}" alt="{{ $photo->title }}" loading="lazy" class="size-full object-cover transition-transform group-hover:scale-105">
                <figcaption class="absolute bottom-3 left-3 rounded-full bg-white px-3 py-1 text-xs font-bold text-navy">{{ $photo->title }}</figcaption>
            </figure>
        @endforeach
    </div>
</section>

<section class="mx-auto mt-10 max-w-6xl px-4">
    <div class="rounded-3xl bg-navy p-6 sm:p-8">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-extrabold tracking-tight text-white">Video Gallery</h2>
            <a class="inline-flex items-center gap-1 text-sm font-bold text-sun hover:underline" href="{{ route('gallery.videos') }}">View all videos <x-lucide-arrow-right class="size-4" /></a>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($videos as $video)
                <x-video-card :video="$video" />
            @endforeach
        </div>
    </div>
</section>

<section class="mx-auto mt-10 max-w-6xl px-4">
    <div class="flex flex-wrap items-center justify-between gap-5 rounded-3xl bg-sun p-7 sm:p-9">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-navy sm:text-3xl">Give your child a place to grow</h2>
            <p class="mt-1 font-medium text-navy">Admission is open. Apply online in a few minutes.</p>
        </div>
        <a href="{{ route('apply') }}" class="rounded-full bg-navy px-7 py-3 font-bold text-white hover:bg-navy-dark">Apply now</a>
    </div>
</section>
@endsection
