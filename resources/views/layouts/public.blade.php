@php
    $menu = [
        'About Us' => [['History', route('page', 'history')], ['Achievements', route('page', 'achievements')], ['Campus Facilities', route('page', 'facilities')], ['Future Plans', route('page', 'future-plans')]],
        'Co-curricular' => [['Scouts', route('page', 'scouts')], ['Debate Club', route('page', 'debate-club')], ['Sports', route('page', 'sports')], ['Computer Lab', route('page', 'computer-lab')], ['Science Club', route('page', 'science-club')], ['Annual Magazine', route('page', 'magazine')]],
        'Administration' => [['Teachers', route('teachers')], ['Governing Body', route('people', 'governing-body')], ['Management Committee', route('people', 'management-committee')], ['Former Head Teachers', route('people', 'former-headmasters')], ['Former Chairpersons', route('people', 'former-presidents')], ['Office Staff', route('office-staff')], ['Donors', route('people', 'donors')], ['Anti-Harassment Committee', route('people', 'harassment-committee')]],
        'Activities' => [['Notices', route('notices.index')], ['Events', route('events')], ['Class Routine', route('routine')], ['Holiday Calendar', route('holidays')], ['Apply Online', route('apply')], ['Transport', route('transport')]],
        'Rules' => [['School Rules', route('page', 'rules')], ['Code of Conduct', route('page', 'discipline')], ['Examination System', route('page', 'exam-system')], ['Uniform', route('page', 'uniform')], ['Admission Process', route('page', 'admission-process')], ['Fees', route('page', 'admission-fees')], ['Fee Payment', route('page', 'fee-payment')]],
        'Students' => [['Top Achievers', route('scholars')], ['Student Cabinet', route('people', 'student-cabinet')], ['Student Sign In', route('student.login')]],
        'Gallery' => [['Photo Gallery', route('gallery.photos')], ['Video Gallery', route('gallery.videos')]],
    ];
    $link = 'rounded-lg px-2.5 py-2 text-sm font-semibold text-navy hover:bg-indigo-50 hover:text-brand-dark';
@endphp
<!doctype html>
<html lang="en">
<head>
    @include('layouts.head')
    <meta name="description" content="{{ setting('school_name') }}: {{ setting('tagline') }}">
    <title>@hasSection('title')@yield('title') · @endif{{ setting('school_name') }}</title>
</head>
<body class="bg-white! text-slate-800" x-data="{ menu: false }">
<div class="bg-navy text-[13px] font-medium text-indigo-100">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-1 px-4 py-2">
        <p class="flex gap-5"><span>EIIN: {{ setting('eiin') }}</span><span>School code: {{ setting('school_code') }}</span></p>
        <p class="flex gap-4">
            <a class="hover:text-white" href="{{ route('student.login') }}">Student sign in</a>
            <a class="hover:text-white" href="{{ route('staff.login') }}">Staff sign in</a>
        </p>
    </div>
</div>

<header class="bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3.5">
            <img src="{{ asset('logo.svg') }}" alt="" class="size-14">
            <span>
                <span class="block text-3xl font-extrabold tracking-tight text-navy">{{ setting('school_name') }}</span>
                <span class="block text-sm text-slate-600">{{ setting('address') }}</span>
            </span>
        </a>
        <div class="hidden items-center gap-7 md:flex">
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('phone')) }}" class="flex items-center gap-2.5">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-indigo-100 text-brand-dark"><x-lucide-phone class="size-4" /></span>
                <span class="text-sm leading-tight"><span class="block text-xs text-slate-500">Call us</span><span class="font-bold text-navy">{{ setting('phone') }}</span></span>
            </a>
            <a href="mailto:{{ setting('email') }}" class="flex items-center gap-2.5">
                <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-teal-100 text-teal-700"><x-lucide-mail class="size-4" /></span>
                <span class="text-sm leading-tight"><span class="block text-xs text-slate-500">Email</span><span class="font-bold text-navy">{{ setting('email') }}</span></span>
            </a>
        </div>
    </div>
</header>

<nav class="sticky top-0 z-40 border-y border-slate-200 bg-white" aria-label="Main">
    <div class="mx-auto max-w-6xl px-4">
        <button type="button" class="flex w-full items-center justify-between py-3 font-semibold text-navy lg:hidden" @click="menu = !menu" :aria-expanded="menu">
            Menu <x-lucide-menu class="size-5" />
        </button>
        <ul class="items-center gap-0.5 py-2 lg:flex" :class="menu ? 'block' : 'hidden'">
            <li><a href="{{ route('home') }}" class="{{ $link }} flex items-center gap-1.5"><x-lucide-house class="size-4" /> Home</a></li>
            @foreach ($menu as $label => $items)
                <li class="relative" x-data="{ open: false }" @mouseleave="open = false">
                    <button type="button" @click="open = !open" @mouseenter="open = true" :aria-expanded="open" class="{{ $link }} flex w-full items-center gap-1">
                        {{ $label }} <x-lucide-chevron-down class="size-3.5" />
                    </button>
                    <ul x-show="open" x-cloak class="z-50 min-w-60 rounded-2xl border border-slate-200 bg-white p-1.5 text-slate-800 shadow-xl lg:absolute lg:left-0">
                        @foreach ($items as [$name, $url])
                            <li><a href="{{ $url }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-indigo-50 hover:text-brand-dark">{{ $name }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
            <li><a href="{{ route('result') }}" class="{{ $link }} block">Results</a></li>
            <li><a href="{{ route('contact') }}" class="{{ $link }} block">Contact</a></li>
            <li class="mt-2 lg:ml-auto lg:mt-0"><a href="{{ route('apply') }}" class="inline-block rounded-full bg-brand px-5 py-2 text-sm font-bold text-white hover:bg-brand-dark">Apply now</a></li>
        </ul>
    </div>
</nav>

<main>@yield('content')</main>

<footer class="mt-14 bg-navy text-indigo-100">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <h2 class="text-lg font-bold text-white">About the school</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a class="hover:text-white" href="{{ route('page', 'history') }}">School history</a></li>
                <li><a class="hover:text-white" href="{{ route('teachers') }}">Our teachers</a></li>
                <li><a class="hover:text-white" href="{{ route('page', 'rules') }}">School rules</a></li>
                <li><a class="hover:text-white" href="{{ route('credits') }}">Photo and video credits</a></li>
            </ul>
        </div>
        <div>
            <h2 class="text-lg font-bold text-white">Quick links</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a class="hover:text-white" href="{{ route('result') }}">Exam results</a></li>
                <li><a class="hover:text-white" href="{{ route('notices.index') }}">Notices</a></li>
                <li><a class="hover:text-white" href="{{ route('apply') }}">Apply for admission</a></li>
                <li><a class="hover:text-white" href="{{ route('gallery.photos') }}">Photo gallery</a></li>
            </ul>
        </div>
        <div>
            <h2 class="text-lg font-bold text-white">Contact</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex gap-2.5"><x-lucide-map-pin class="mt-0.5 size-4 shrink-0" /> {{ setting('address') }}</li>
                <li class="flex gap-2.5"><x-lucide-phone class="mt-0.5 size-4 shrink-0" /> {{ setting('phone') }}</li>
                <li class="flex gap-2.5"><x-lucide-mail class="mt-0.5 size-4 shrink-0" /> {{ setting('email') }}</li>
            </ul>
        </div>
    </div>
    <div class="bg-navy-dark py-4 text-center text-sm">© {{ now()->year }} {{ setting('school_name') }}. All rights reserved.</div>
</footer>
</body>
</html>
