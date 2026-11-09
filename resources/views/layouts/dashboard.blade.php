<!doctype html>
<html lang="en">
<head>
    @include('layouts.head')
    <title>@yield('title', 'Dashboard') · {{ setting('school_name') }}</title>
</head>
@php
    $user = auth()->user();
    $menu = \App\Support\Navigation::for($user);
    $now = request()->url();
    $isActive = fn ($url) => $now === $url || str_starts_with($now, $url.'/');
    $roleLabel = ['admin' => 'Administrator', 'teacher' => 'Teacher', 'student' => 'Student'][$user->role];
@endphp
<body x-data="{ side: false, q: '' }">
<div class="flex min-h-screen">
    <div x-show="side" x-cloak @click="side = false" class="no-print fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    <aside :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="no-print fixed inset-y-0 left-0 z-40 flex w-68 -translate-x-full flex-col transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
           style="background: linear-gradient(180deg, #1c1d52 0%, #13143a 100%)">
        <a href="{{ route($user->homeRoute()) }}" class="flex items-center gap-3 px-5 py-5">
            <img src="{{ asset('logo.svg') }}" alt="" class="size-10">
            <span class="leading-tight">
                <span class="block text-[17px] font-extrabold text-white">{{ setting('school_name') }}</span>
                <span class="block text-xs text-indigo-200/80">{{ $roleLabel }} panel</span>
            </span>
        </a>

        @if (count($menu) > 12)
            <div class="px-4 pb-3">
                <label class="relative block">
                    <span class="sr-only">Find a page</span>
                    <x-lucide-search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-indigo-200/70" />
                    <input x-model="q" type="search" placeholder="Find a page..." class="w-full rounded-xl border border-white/10 bg-white/8 py-2 pl-9 pr-3 text-sm text-white placeholder:text-indigo-200/60 focus:border-white/30 focus:outline-none">
                </label>
            </div>
        @endif

        <nav class="sidebar-scroll flex-1 space-y-1 overflow-y-auto px-3 pb-4" aria-label="Main menu">
            @foreach ($menu as $item)
                @if (isset($item['children']))
                    @php
                        $open = collect($item['children'])->contains(fn ($c) => $isActive($c['url']));
                        $labels = mb_strtolower($item['label'].' '.collect($item['children'])->pluck('label')->implode(' '));
                    @endphp
                    <div x-data="{ open: @js($open) }" x-show="!q || @js($labels).includes(q.toLowerCase())">
                        <button type="button" @click="open = !open" :aria-expanded="open || !!q" class="side-link">
                            <x-dynamic-component :component="'lucide-'.$item['icon']" class="size-5 shrink-0" />
                            <span class="flex-1">{{ $item['label'] }}</span>
                            <span class="transition-transform" :class="(open || q) && 'rotate-180'"><x-lucide-chevron-down class="size-4" /></span>
                        </button>
                        <div x-show="open || q" x-cloak class="ml-5 mt-1 space-y-0.5 border-l border-white/10 pl-3">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] }}" x-show="!q || @js(mb_strtolower($item['label'].' '.$child['label'])).includes(q.toLowerCase())" @class(['side-sub', 'active' => $isActive($child['url'])]) @if($isActive($child['url'])) aria-current="page" @endif>{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item['url'] }}" x-show="!q || @js(mb_strtolower($item['label'])).includes(q.toLowerCase())" @class(['side-link', 'active' => $isActive($item['url'])]) @if($isActive($item['url'])) aria-current="page" @endif>
                        <x-dynamic-component :component="'lucide-'.$item['icon']" class="size-5 shrink-0" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 p-3">
            @csrf
            <button class="side-link"><x-lucide-log-out class="size-5" /> Sign out</button>
        </form>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="no-print sticky top-0 z-20 flex items-center gap-3 border-b border-line bg-surface/90 px-4 py-3 backdrop-blur sm:px-6">
            <button type="button" @click="side = true" class="btn btn-sm lg:hidden" aria-label="Open menu"><x-lucide-menu class="size-5" /></button>

            @if ($user->role !== 'student')
                <form action="{{ staff_route($user->role === 'admin' ? 'students.index' : 'students') }}" class="relative hidden max-w-md flex-1 sm:block" role="search">
                    <x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-3" />
                    <input name="q" type="search" value="{{ request()->routeIs('*.students*') ? request('q') : '' }}" aria-label="Search students" placeholder="Search students by name, ID or phone..." class="input pl-10">
                </form>
            @else
                <p class="hidden text-sm text-ink-2 sm:block">{{ now()->format('l, F j, Y') }}</p>
            @endif

            <div class="ml-auto flex items-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-sm hidden md:inline-flex"><x-lucide-external-link class="size-4" /> Website</a>
                <button type="button" x-data="themeToggle" @click="toggle" class="btn btn-sm" :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                    <span x-show="!dark"><x-lucide-moon class="size-4" /></span>
                    <span x-show="dark" x-cloak><x-lucide-sun class="size-4" /></span>
                </button>
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex items-center gap-2.5 rounded-xl px-2 py-1 hover:bg-surface-2">
                        <x-avatar :name="$user->name" :photo="$user->employee?->photo ?? $user->student?->photo" class="size-9" />
                        <span class="hidden text-left leading-tight sm:block">
                            <span class="block text-sm font-bold">{{ $user->name }}</span>
                            <span class="block text-xs text-ink-3">{{ $roleLabel }}</span>
                        </span>
                        <x-lucide-chevron-down class="hidden size-4 text-ink-3 sm:block" />
                    </button>
                    <div x-show="open" x-cloak class="card absolute right-0 mt-2 w-48 overflow-hidden p-1.5 shadow-xl">
                        @if ($user->role !== 'admin')
                            <a href="{{ route($user->role === 'student' ? 'portal.profile' : 'teacher.profile') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-surface-2">My profile</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm text-rose-600 hover:bg-surface-2 dark:text-rose-400">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="print-reset flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="no-print border-t border-line px-6 py-4 text-sm text-ink-3">© {{ now()->year }} {{ setting('school_name') }}. All rights reserved.</footer>
    </div>
</div>

@if (session('ok') || session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)" role="status"
         class="no-print fixed bottom-5 right-5 z-50 flex max-w-sm items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-xl {{ session('error') ? 'bg-rose-600' : 'bg-emerald-700' }}">
        @if (session('error')) <x-lucide-circle-alert class="size-5 shrink-0" /> @else <x-lucide-circle-check class="size-5 shrink-0" /> @endif
        <span>{{ session('error') ?? session('ok') }}</span>
        <button type="button" @click="show = false" aria-label="Dismiss"><x-lucide-x class="size-4" /></button>
    </div>
@endif
</body>
</html>
